package GerCo::GlobalVariables ;

#
# GlobalVariables.pm
#
# Declaration of global variables of GerCo package
#

use strict ;
use utf8 ;
use warnings ;

use Encode qw(encode decode) ;
use POSIX qw/mktime strftime difftime locale_h/ ;

# use JSON::XS qw( decode_json ) ;
# use Text::Haml ;

require Exporter ;
our @ISA = 'Exporter' ;
our @EXPORT = qw( %theLots ) ;

our %theLots = () ;

1;
