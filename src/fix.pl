#!/usr/bin/perl
#
# radiholic::autofix Cron Script
#
# (c) Kazuya Shimanuki 2010
#

use strict;
use Log::Log4perl qw(:easy);

use constant MAX_PROCESSES => 1;

#
# Init
#

Log::Log4perl->easy_init({level => $INFO,
        layout => "%d [%P] %p> %L:\t%m%n",
        file => ">> :utf8> killed.log" });

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

sub get_process_etime {
    my $key = shift;
    my @ps  = `/bin/ps --no-headers -ww -eo pid,etime,args --sort etime | /bin/grep "$key" | /bin/grep -v grep`;
    foreach my $line (@ps) {
        #chomp $line;
        #25410 5000           45:51 /usr/bin/perl /home/radiholic/radio_convert.pl
        if ($line =~ /^\s*(\d+)\s+?(.+)\s+?(.+)/) {
            my($pid,$user,$etime) = ($1,$2,$3);
            return ($pid,$user);
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



#
# check running process
#

if ( get_process_num() > MAX_PROCESSES ) {
    $logger->error("Over Max Processes");
    exit;
}

my ($pid,$etime) = get_process_etime('/home/radiholic/radio_convert.pl');
if ( $etime )
{
    print "$pid $etime\n";
}
else 
{
    print "notfound\n";
}

$logger->info("All ok, exit.");

