package GerCo::Lots ;

use utf8 ;
use strict ;
use warnings ;

use Exporter ;
our @ISA = 'Exporter' ;
our @EXPORT = qw( &readLots &countSpecial &sortLots) ; 

use GerCo::GlobalVariables ;
use Data::Dumper ;

#====================================================================================
	sub readLots
#====================================================================================
#
#	Lit la liste des lots
#
#====================================================================================
{
my ($filename,$objectsArrayRef) = @_ ;

open ( my $fh, '<:encoding(UTF-8)', $filename ) || die ("Le fichier $filename n'a pas été trouvé" ) ;

my $line ;
my $key, my $value ;
my $lot, my $batiment, my $type, my $numero, my $general, my $special, my $handicap, 
my $double ;
my @numeros ;
my $new = 0 ;

while ( <$fh> )
	{
	chomp () ;
	$line = $_ ;
	unless ( $line =~ /^#/ )
		{
		if ( $line =~ /\S+/ )
			{
			($key,$value) = split (/:/ ) ;
			$key =~ s/^\s+// ;
			$key =~ s/\s+$// ;
			$value =~ s/^\s+// ;
			$value =~ s/\s+$// ;
	
			if ( $key =~ /Lot/ )
				{
				$lot = $value ;
				@numeros = () ;
				$new = 1 ;
				}
			elsif ( $key =~ /Bat/ )	
				{
				$batiment = $value ;
				}
			elsif ( $key =~ /Type/ )	
				{
				$type = $value ;
				}
			elsif ( $key =~ /Numero/ )	
				{
				@numeros = split (/\s+/, $value) ;
				}
			elsif ( $key =~ /General/ )	
				{
				$general = $value ;
				}
			elsif ( $key =~ /Special/ )	
				{
				$special = $value ;
				}
			elsif ( $key =~ /Handicap/ )	
				{
				$handicap = $value ;
				}
			elsif ( $key =~ /Double/ )	
				{
				$double = $value ;
				}
			}
		elsif ( $new == 1 )
			{
			$new = 0 ;

			if ( !defined ($handicap))
				{
				$handicap = 0 ;
				}
			if ( !defined ($double))
				{
				$double = 0 ;
				}

			my @array = @numeros ;					
			my %lotObject =
				( 'lot' => $lot ,
				'batiment' => $batiment ,
				'type' => $type ,
				'general' => $general ,
				'special' => $special,
				'handicap' => $handicap,
				'double' => $double,
				'numeros' => \@array ) ;
			$objectsArrayRef -> {$lot} = \%lotObject ;	
			$handicap = undef ;
			$double = undef ;
			}	
		}
	}
	
close ($fh) ;
}

#====================================================================================
	sub countSpecial
#====================================================================================
{
my ($lotsArrayRef) = @_ ;
my $sum = 0 ;

my %lotsArray = %$lotsArrayRef ;

foreach my $key (keys(%$lotsArrayRef))
	{
 	print "$key\n" ;
 	$sum += $lotsArrayRef->{$key}->{special} ;
 	}

my $total = $sum/10000*100 ;
print "Special $sum $total\n"	; 
}

#====================================================================================
	sub sortLots
#====================================================================================
{
my ($lotsArrayRef) = @_ ;

my @sortedKeys = sort ( { $lotsArrayRef->{$a}->{general} <=> $lotsArrayRef->{$b}->{general} } keys(%$lotsArrayRef) ) ;
# my @sortedKeys = sort ( { $a <=> $b } keys(%theLots) ) ;
print Dumper (@sortedKeys) ;

foreach my $key (@sortedKeys)
	{
	my $value = $lotsArrayRef->{$key} -> {general} ;
	print "Lot $key : \t $value\n" ;
	}
}

1 ;