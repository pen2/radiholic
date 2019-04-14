#!/usr/bin/perl
#
# radiholic::radio_convert Cron Script
#
# (c) Kazuya Shimanuki 2008
#

use strict;
use DBI;
use Digest::MD5 qw(md5 md5_hex md5_base64);
use Log::Log4perl qw(:easy);

use Net::Twitter;
use Jcode;

use constant MAX_PROCESSES => 2;
use constant MAX_FILE_SIZE => 83000; # 40G Bytes

#
# Init
#

Log::Log4perl->easy_init({level => $INFO,
        layout => "%d [%P] %p> %L:\t%m%n",
        file => ">> :utf8> convert.log" });

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
if ( get_files_size() > MAX_FILE_SIZE ) {
    $logger->error("Over Max File Sizes");
    exit;
}
my $cache_size = get_files_size();

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
    WHERE `state` = 'new'
    ORDER BY `req_date` ASC ");
if(!$sth->execute){
    $logger->error("Failed Get Requests");
    exit;
}

# 
# Fetch and check url
# 

my @rec = $sth->fetchrow_array;
my $id  = $rec[0];
my $url = $rec[1];
my $bra = $rec[2]; # branch
my $tle = $rec[3]; # title
my $ses = $rec[4]; # session

if ( !check_url($url) ) {
    $logger->debug("Nothing Todo, exit.");
    exit;
}
$sth->finish;
$logger->info("New task found. Now cache size: $cache_size MBytes");

#
# Start convert
#

my $md5 = md5_hex("$url-$bra"); # branch
my $filename_wav = "$md5.wav";
my $filename_mp3 = "$md5.mp3";
$logger->info("Converting id:$id, url:'$url', md5(url).wav:$filename_wav, branch:$bra"); # branch
$url = escape_shell($url);
$logger->info("Escaped url to:'$url'");


# 
# Flag Converting
# 
my $sth = $db->prepare("UPDATE `entry` 
    SET `state`='converting' WHERE `id`='$id' ");
if(!$sth->execute){
    $logger->error("Write Converting flag error. Nothing Todo");
    exit;
}
$sth->finish;

my @mplayer = `/usr/local/bin/mplayer -ao pcm:fast:file=files/$filename_wav -playlist $url 2>&1`;

# Moved check
# add output in "stream/asf_streaming.c"
my $new_url;
foreach my $line (@mplayer) { 
    if ($line =~ /^Moved: Using this url instead (.+)$/) {
        $new_url = $1;
        next;
    }
}
if ($new_url ne '') {
    $logger->info("Resource moved id:$id, url:'$url'->'$new_url'");
    @mplayer = `/usr/local/bin/mplayer -cache 1000 -ao pcm:fast:file=files/$filename_wav $new_url 2>&1`;
}

my $name = "";
my $author = "Unknown";
my $copyright = "Unknown";

foreach my $line (@mplayer) { 
    #chomp $line;
    if ($line =~ /^ name: (.+)$/) {
        $name = $db->quote($1);
        next;
    }
    if ($line =~ /^ author: (.+)$/) {
        $author = $db->quote($1);
        next;
    }
    if ($line =~ /^ copyright: (.+)$/) {
        $copyright = $db->quote($1);
        next;
    }
}

#
# Exist check
#
if (-e "files/$filename_wav") {
    $logger->info("Success convert id:$id, url:'$url', name:'$name'");
}
else {
    $logger->warn("Failed convert id:$id, url:'$url'");

    my $sth = $db->prepare("UPDATE `entry` 
        SET `state`='failed', 
        `filename`='$filename_wav', 
        `name`='$name', 
        `author`='$author', 
        `copyright`='$copyright' 
        WHERE `id`='$id' ");
    if(!$sth->execute){
        exit;
    }
    $sth->finish;
    exit;
}

# 
# Flag Packaging
# 
my $sth = $db->prepare("UPDATE `entry` 
    SET `state`='packaging', 
    `filename`= ?, 
    `name`= ?, 
    `author`= ?, 
    `copyright`= ? 
    WHERE `id`= ? ");
if(!$sth->execute($filename_wav, $name, $author, $copyright, $id)){
    $logger->error("Write Packaging flag error. TODO: Remove converted file");
    exit;
}
$sth->finish;

my $lame = system("/usr/local/bin/lame --quiet -b 96kbps -m m files/$filename_wav files/$filename_mp3 2>&1");


# 
# Remove temp file
# 
unlink "files/$filename_wav";

if ($lame != 0) {
    $logger->error("Lame packaging error. returns $lame");
    exit;
}


# 
# Flag Packaging
# 
my $sth = $db->prepare("UPDATE `entry` 
    SET `state`='success', 
    `filename`='$filename_mp3', 
    `fin_date`=NOW() 
    WHERE `id`='$id' ");
if(!$sth->execute){
    $logger->error("Write Packaging flag error. TODO: Remove converted file");
    exit;
}
$sth->finish;

$db->disconnect;

$logger->info("Success package id:$id, url:'$url', exit.");

