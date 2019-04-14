#!/usr/bin/perl
#
# radiholic::archive_upload Cron Script
#
# (c) Kazuya Shimanuki 2009
#

use strict;
use DBI;
use Digest::MD5 qw(md5 md5_hex md5_base64);
use Log::Log4perl qw(:easy);

use Net::Twitter;
use Jcode;

use constant MAX_PROCESSES => 1;
use constant MAX_FILE_SIZE => 52000; # 40G Bytes

#
# Init
#

Log::Log4perl->easy_init({level => $INFO,
        layout => "%d [%P] %p> %L:\t%m%n",
        file => ">> :utf8> upload.log" });

our $logger = get_logger();

#
# User functions
#

sub production_check {
    my $host = `hostname`;
    if ($host =~ /^.*bianca.*$/) {
        return 1;
    }
    return 0;
}

sub escape_shell {
    $_ = shift;
    # escape chars
    # & ; ` ' \ " | * ? ~ < > ^ ( ) [ ] { } $ \n \r
    s/([\&\;\`\'\\\"\|\*\?\~\<\>\^\(\)\[\]\{\}\$\n\r])/\\$1/g;
    return $_;
}

sub get_files_size {
    my @du  = `/usr/bin/du -m files/`;
    foreach my $line (@du) {
        #chomp $line;
        if ($line =~ /^(\d+)\s.*$/) {
            return $1;
        }
    }
    return 0;
}

sub get_process_num {
    my @ps = `/bin/ps x`;
    my $script_name = $0;
    my $process_num = 0;

    foreach my $line (@ps) { 
        #chomp $line;
        if ($line =~ /^.+perl $script_name$/) {
            $process_num++;
        }
    }

    $logger->debug("Start '$script_name': $process_num running.");
    return $process_num;
}

sub check_url {
    my ($url) = @_;

    # url check
    if ($url =~ /^http:\/\/.+$/) {
        return 1;
    }
    # not url
    return 0;
}


#
# check running process
#

if ( get_process_num() > MAX_PROCESSES ) {
    $logger->error("Over Max Processes");
    exit;
}

#
# Connect DB
#
my $db = DBI->connect("DBI:mysql:radiholic","radiholic","",
    {RaiseError => 0, PrintError => 1}
    );
if(!$db){
    $logger->error("DB Connect Error");
    exit;
}
my $sth = $db->prepare("SET NAMES utf8");
if(!$sth->execute){
    $logger->error("Failed Set UTF-8");
    exit;
}
$sth->finish;

# 
# Get Requests
# 
my $sth = $db->prepare("SELECT * 
    FROM `entry` 
    WHERE (`state` = 'success' OR `state` = 'hidden')
    AND `req_date` < 20160631
    AND `count` <= 450
    ORDER BY `req_date` ASC ");
if(!$sth->execute){
    $logger->error("Failed Get Requests");
    exit;
}
#    AND `req_date` < 20100215
#    AND `count` <= 16

# 
# Fetch and check url
# 

my @rec = $sth->fetchrow_array;
my $id  = $rec[0];
my $url = $rec[1];
my $bra = $rec[2]; # branch
my $tle = $rec[3]; # title
my $ses = $rec[4]; # session
my $rdt = $rec[7]; # req_date
my $fnm = $rec[10]; # filename

if ( !check_url($url) ) {
    $logger->debug("Nothing Todo, exit.");
    exit;
}
$sth->finish;
$logger->info("New task found. ");

#
# Start upload
#

$logger->info("Uploading id:$id, url:'$url', branch:$bra, req_date:$rdt, filename=files/$fnm");

#
# Exist check
#
if (-e "files/$fnm") {
    # exist ok
}
else {
	$logger->warn("Failed Upload id:$id, url:'$url', branch:$bra, req_date:$rdt, filename=files/$fnm, res=local_file_not_found'");

	# reset success(will re-try)
	my $sth = $db->prepare("UPDATE `entry` 
			SET `state`='success', WHERE `id`='$id' ");
	if(!$sth->execute){
		exit;
	}
	$sth->finish;
	exit;
}

# 
# Flag uploading
# 
my $sth = $db->prepare("UPDATE `entry` 
    SET `state`='uploading' WHERE `id`='$id' ");
if(!$sth->execute){
    $logger->error("Write Uploading flag error. Nothing Todo");
    exit;
}
$sth->finish;

my @es_post = `/home/radiholic/es_post.pl files/$fnm 2>&1`;
my $es_post_res = "uk";
my $es_post_state = 0;

foreach my $line (@es_post) { 
    #chomp $line;
    if ($line =~ /^(ok: .+)$/) {
        $es_post_res = $db->quote($1);
	$es_post_state = 1;
        next;
    }
    if ($line =~ /^(err: .+)$/) {
        $es_post_res = $db->quote($1);
	$logger->warn("Failed Upload id:$id, url:'$url', branch:$bra, req_date:$rdt, filename=files/$fnm, res=$es_post_res'");

	# reset success(will re-try)
	my $sth = $db->prepare("UPDATE `entry` 
			SET `state`='success', WHERE `id`='$id' ");
	if(!$sth->execute){
		exit;
	}
	$sth->finish;
	exit;
    }
}
# TODO: if no ok no err pattern!


# 
# Remove data file
# 

if ($es_post_state) {
    $logger->info("Success upload. id:$id, url:'$url', branch:$bra, req_date:$rdt, filename=files/$fnm");
    unlink "files/$fnm";
    $logger->info("Remove ok. id:$id");
}
else{
    $logger->error("Upload did not success. not remove files/$fnm");
    exit;
}


# 
# Flag Archive
# 
my $sth = $db->prepare("UPDATE `entry` 
    SET `state`='archived' WHERE `id`='$id' ");
if(!$sth->execute){
    $logger->error("Write Archive flag error.");
    exit;
}
$sth->finish;

$db->disconnect;

$logger->info("All ok. id:$id, exit.");

