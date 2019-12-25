#!/usr/bin/env perl

use strict ;
use utf8 ;
use Data::Dumper ;


use lib "/Users/regis/Documents/GerCo/perl" ;
use GerCo::GlobalVariables ;
use GerCo::Lots ;

readLots ("../00-data/00-lots.txt", \%theLots) ;
#print Dumper(%theLots) ;

countSpecial (\%theLots) ;
sortLots (\%theLots) ;


