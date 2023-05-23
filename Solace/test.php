<?php

$services=[['A','R-A'],['B','R-B'],['C','R-C'],['D','R-D'],['E','R-E']];

for($i=0;i<count($services);$i++)
{
    echo "serviceId=".$services[$i][0];

    for($j=0;j<count($services);$j++)
    {

    }
}
/*
initiators: 

BCDE
BCDE
CCDE
DDDE
EEEE

Remote : 

AAAA
ABBB
ABCC
ABCD
ABCD
*/