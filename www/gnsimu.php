<?php
/**
 * GALAXY-NETWORK Browsergame - Simulator
 *
 * @link http://www.galaxy-network.net/game/login.php
 *
 * @package zorg\Games\GalaxyNetwork
 */

/**
 * Galaxy-Network Simulator
 */
class GNSimu
{
    var $attaking;   // Angreifende Schiffe(array von 0-8)

    var $deffending; // Verteidigende Schiffe(array von 0-13)

    var $Oldatt;     //Schiffe die am Anfang des Ticks da waren

    var $Olddeff;

    var $mexen;      // Exen die der Angegriffene hat

    var $kexen;

    var $stolenmexen;  // Geklauteexen

    var $stolenkexen;

    var $gesstolenexenm; // Gesammtgeklaute exen

    var $gesstolenexenk;

    var $geslostshipsatt; // Schiffe die seit erstellung der Klasse zerstört wurden

    var $geslostshipsdeff;

    var $mcost;            // Wie viel ein Schiff Kostet

    var $kcost;

    var $name;

    var $attakpower;

    var $shiptoattak;

    var $percent;



    function __construct() // Variablen mit Kampfwerten füllen
    {
        // Daten Für Jäger Nr. 0
        $this->name[0] = "Jäger";

        $this->attakpower[0]  = array(0.0246, 0.392, 0.0263); // Wie viele Schiffe ein Schiff mit 100% Feuerkrafft zerstören würde

        $this->shiptoattak[0] = array(11,1,4); // Welche Schiffe/Geschütze angegriffen werden

        $this->percent[0]     = array(0.35,0.30,0.35); // Die Verteilung der Prozente, mit der auf die Schiffe geschoßen wird.

        $this->mcost[0] = 4000;

        $this->kcost[0] = 6000;



        // Daten Für Bomber Nr. 1

        $this->attakpower[1]  = array(0.0080,0.0100,0.0075);

        $this->shiptoattak[1] = array(12,5,6);

        $this->percent[1]     = array(0.35,0.35,0.30);

        $this->name[1] = "Bomber";

        $this->mcost[1] = 2000;

        $this->kcost[1] = 8000;



        // Daten Für Fregatte Nr. 2

        $this->attakpower[2]  = array(4.5,0.85);

        $this->shiptoattak[2] = array(13,0);

        $this->percent[2]     = array(0.6,0.4);

        $this->name[2] = "Fregatte";

        $this->mcost[2] = 15000;

        $this->kcost[2] = 7500;



        // Daten Für Zerstörer Nr. 3

        $this->attakpower[3]  = array(3.5,1.2444);

        $this->shiptoattak[3] = array(9,2);

        $this->percent[3]     = array(0.6,0.4);

        $this->name[3]     = "Zerstörer";

        $this->mcost[3] = 40000;

        $this->kcost[3] = 30000;



        // Daten Für Kreuzer Nr. 4

        $this->attakpower[4]  = array(2.0,0.8571,10.0);

        $this->shiptoattak[4] = array(10,3,8);

        $this->percent[4]     = array(0.35,0.30,0.35);

        $this->name[4]       = "Kreuzer";

        $this->mcost[4] = 65000;

        $this->kcost[4] = 85000;



        // Daten Für Schalchtschiff Nr. 5

        $this->attakpower[5]  = array(1.0,1.0666,0.4,0.3019,26.6667);

        $this->shiptoattak[5] = array(11,4,5,6,8);

        $this->percent[5]     = array(0.2,0.2,0.2,0.2,0.2);

        $this->name[5]     = "Schlachtschiff";

        $this->mcost[5] = 250000;

        $this->kcost[5] = 150000;



        // Daten Für Trägerschiff Nr. 6

        $this->attakpower[6]  = array(25.0,14.0);

        $this->shiptoattak[6] = array(7,8);

        $this->percent[6]     = array(0.5,0.5);

        $this->mcost[6] = 200000;

        $this->kcost[6] =  50000;

        $this->name[6]     = "Trägerschiff";





        // Daten für Kaperschiff

        $this->mcost[7] = 1500;

        $this->kcost[7] = 1000;

        $this->name[7] = "Kaperschiff";





        // Daten für Schutzschiff

        $this->mcost[8] = 1000;

        $this->kcost[8] = 1500;

        $this->name[8]    = "Schutzschiff";





        // Daten Für Leichtes Obligtalgeschütz Nr. 9

        $this->attakpower[9]  = array(0.3,1.28);

        $this->shiptoattak[9] = array(0,7);

        $this->percent[9]     = array(0.6,0.4);

        $this->mcost[9] = 6000;

        $this->kcost[9] = 2000;

        $this->name[9]    = "Leichtes Obligtalgeschütz";





        // Daten Für Leichtes Raumgeschütz Nr. 10

        $this->attakpower[10]  = array(1.2,0.5334);

        $this->shiptoattak[10] = array(1,2);

        $this->percent[10]     = array(0.4,0.6);

        $this->mcost[10] = 20000;

        $this->kcost[10] = 10000;

        $this->name[10]     = "Leichtes Raumgeschütz";





        // Daten Für Mittleres Raumgeschütz Nr. 11

        $this->attakpower[11]  = array(0.9143,0.4267);

        $this->shiptoattak[11] = array(3,4);

        $this->percent[11]     = array(0.4,0.6);

        $this->mcost[11] =  60000;

        $this->kcost[11] = 100000;

        $this->name[11]     = "Mittleres Raumgeschütz";





            // Daten Für Schweres Raumgeschütz Nr. 12

        $this->attakpower[12]  = array(0.5,0.3774);

        $this->shiptoattak[12] = array(5,6);

        $this->percent[12]     = array(0.5,0.5);

        $this->mcost[12] = 200000;

        $this->kcost[12] = 300000;

        $this->name[12]     = "Schweres Raumgeschütz";





        // Daten Für  Abfangjäger Nr. 13

        $this->attakpower[13]  = array(0.0114,0.32);

        $this->shiptoattak[13] = array(3,7);

        $this->percent[13]     = array(0.4,0.6);

        $this->mcost[13] = 1000;

        $this->kcost[13] = 1000;

        $this->name[13]     = "Abfangjäger";

        }







        function Compute($lasttick) // Dieses ist sie also die mytische Funktion, die Werte in äh ja äh andere Werte verwandeln kann. $lasttick dient dazu im letzten tick die Jäger und Bomber zu zerstören, die über sind.

        {

            // Wenn man tolle Debug informationen sehen will einfach auf 1 setzen

        $debug = (DEVELOPMENT===true ? 1 : 0);





        // "Sicherheitskopie" der Anzahl der Schiffe machen

        for($i=0;$i<14;$i++)

        {

            $this->Olddeff[$i] = $this->deffending[$i];

            if($i<9)

            $this->Oldatt[$i] = $this->attaking[$i];

        }



            //Schleife über alle Schiffe

        for($i=0;$i<14;$i++)

        {

                //Variablen für das nächste Schiff "nullen"

            $RestPercentatt = 0;

            $Restpoweratt = $this->Oldatt[$i]; //Die Power ist gleich der Anzahl der Schiffe die angreifen

            $OldRestpoweratt = 0;

            $RestPercentdeff = 0;

            $Restpowerdeff = $this->Olddeff[$i];

            $OldRestpowerdeff = 0;

            $strike=0;



                //Berechnen wie viele Strikes der aktuelle Schiffstyp hat(eins geteilet durch den kleinsten Prozentwert, mit dem das Schiff feuert und das ganz aufrunden und noch +3)

                if($this->percent[$i])

                    $curstrikes = ceil(1/min($this->percent[$i]))+3;

                else

                    $curstrikes = 0;



            while($strike < $curstrikes )

            {

            if($debug)

                echo "<b>Strike".($strike-$curstrikes)."</b><br>";

                $OldRestpoweratt = $Restpoweratt;

                $OldRestpowerdeff = $Restpowerdeff;

                    // Schleife über alle Schiffe die angeriffen werden sollen

            for($j=0;$j<count($this->attakpower[$i]);$j++)

             {

                if($debug)

                echo $this->name[$i]." gegen ".$this->name[$this->shiptoattak[$i][$j]]."<br>";



                    // Angreifer

                if($Restpoweratt>0)

                {

                    $del = 0;

                            // Dafür sorgen, dass nicht mit einem Prozentsatz von größer als 100% angerifen wird

                    if($RestPercentatt+$this->percent[$i][$j] > 1)

                    $RestPercentatt = 1.0 - $this->percent[$i][$j];



                             // Maximale Zerstörung die Angerichtet werden kann. Die Power der Prozentsatz mal die Power der Schiffe mal wie viele Schiffe vom andern tyo von einem zerstört werden

                    $MaxDestruction = floor(($RestPercentatt+$this->percent[$i][$j]) * $OldRestpoweratt * $this->attakpower[$i][$j]);



                    if($debug)

                    {

                        echo "<font color=#ff0000>-</font> Angreifende Schiffe: ".$this->Oldatt[$i]." Verteidigende Schiffe:".($this->deffending[$this->shiptoattak[$i][$j]])."<br>";

                    echo "<font color=#ff0000>-</font> Maximale Zerstörung: floor(($RestPercentatt+".$this->percent[$i][$j].") * $OldRestpoweratt * ".$this->attakpower[$i][$j].")=$MaxDestruction<br>";

                    }



                            // Wie viele Schiffe dann zerstört werden, nich mehr als die maximale zerstörung und nich mehr als mit 100%(was oben eigentlich schon geprüft wird) und nich mehr als Schiffe noch über sind.

                    $del= floor(max(min($MaxDestruction, $Restpoweratt * $this->attakpower[$i][$j], $this->deffending[$this->shiptoattak[$i][$j]]), 0));





                            // Im 4ten Strike wird unter bestimmten Umständen(s.u) der Prozentsatz, der beim feuern nicht zum Einsatz gekommen ist zu einer Variable addiert, die zum normalen Prozentsatz dazugerechnet wird.

                if($strike==3)

                {

                                // Wenn es das letzte Schiff im Tick ist oder keine Schiffe zerstört wurden wird Rest-Prozent um den Prozentsatz, der nich verbraucht wird erhöht.

                                // Alles könnte schön und gut sein, wenn da nicht die Schlachter wären, die flogen der Regel nämlich nur wenn sie auf sich selbst oder Kreuzer schießen, sonnst wird immer der Prozentsatz der nicht gebraucht wurde dazugerechnet, warum auch immer...

                                if ( $j == count($this->attakpower[$i])-1 || $del == 0 || ($i == 5 && $this->shiptoattak[$i][$j]!=5 && $this->shiptoattak[$i][$j]!=4))

                                {

                        $RestPercentatt += $this->percent[$i][$j] - ($del / $OldRestpoweratt / $this->attakpower[$i][$j]);

                                }

                }

                            // Benutze Feuerkraft berechnen und subtrahiren

                    $Firepower = $del/$this->attakpower[$i][$j];

                    $Restpoweratt -= $Firepower;



                            // Schiffe zerstören

                    $this->deffending[$this->shiptoattak[$i][$j]] -=$del;

                    $this->geslostshipsdeff[$this->shiptoattak[$i][$j]] += $del;



                        if($debug)

                        {

                    echo "<font color=#ff0000>-</font> Zerstörte Schiffe: $del<br>

                                <font color=#ff0000>-</font> Benutzte Firepower = $del/".$this->attakpower[$i][$j]." = $Firepower; Restpower = $Restpoweratt<br>";

                    }

                }





                    // Nochmal genau das selbe nur mit Angreifer/Verteidiger vertauschten Variablen.

                if($Restpowerdeff>0)

                {

                    $del = 0;

                    if($RestPercentdeff+$this->percent[$i][$j] > 1)

                    $RestPercentdeff = 1.0 - $this->percent[$i][$j];

                    $MaxDestruction = floor(($RestPercentdeff+$this->percent[$i][$j]) * $OldRestpowerdeff * $this->attakpower[$i][$j]);



                    if($debug)

                    {

                    echo "<font color=#00ff00>-</font> Angreifende Schiffe: ".$this->Olddeff[$i]." Verteidigende Schiffe:".($this->attaking[$this->shiptoattak[$i][$j]])."<br>";

                    echo "<font color=#00ff00>-</font> Maximale Zerstörung: floor(($RestPercentdeff+".$this->percent[$i][$j].") * $OldRestpowerdeff * ".$this->attakpower[$i][$j].")=$MaxDestruction<br>";



                    }



                    $del= floor(max(min($MaxDestruction, $Restpowerdeff * $this->attakpower[$i][$j], $this->attaking[$this->shiptoattak[$i][$j]]), 0));





                if($strike==3)

                {

                    if ( $j == count($this->attakpower[$i])-1 || $del == 0 || ($i == 5 && $this->shiptoattak[$i][$j]!=5 && $this->shiptoattak[$i][$j]!=4))

                    {
                    $RestPercentdeff += $this->percent[$i][$j] - ($del / $OldRestpowerdeff / $this->attakpower[$i][$j]);

                                }

                }



                    $Firepower = $del/$this->attakpower[$i][$j];

                    $Restpowerdeff -= $Firepower;



                    $this->attaking[$this->shiptoattak[$i][$j]] -= $del;

                    $this->geslostshipsatt[$this->shiptoattak[$i][$j]] += $del;



                        if($debug)

                        {

                    echo "<font color=#00ff00>-</font> Zerstörte Schiffe: $del<br>

                                <font color=#00ff00>-</font> Benutzte Firepower = $del/".$this->attakpower[$i][$j]." = $Firepower; Restpower = $Restpowerdeff<br>";

                    }

                }

            }

            $strike++;

            }

        }



            //Wenn wir im letzen Tick sind wird geprüft ob auch alle Jäger und Bomber mit nach hause fliegn dürfen



        if($lasttick)

        {

            $jaeger =  $this->attaking[0];

            $bomber =  $this->attaking[1];

            $traeger = $this->attaking[6];

            if ( $bomber + $jaeger > $traeger*100)

            {

            $todel = $jaeger + $bomber - $traeger*100;

            $this->attaking[0] -= round($todel*($jaeger/($jaeger + $bomber)));

            $this->attaking[1] -= round($todel*($bomber/($jaeger + $bomber)));

            }

        }

            //Dann noch mal eben schnell paar exen klauen



            //Erstmall ausrechnen, wie viele maximal mitgenommen werden können, bin der Meinung mal Iregndwo im Forum gelesen zu haben, dass Metall- auf- und Kristallexen abgerundet werden

        $maxmexen = ceil((max($this->attaking[7]-$this->deffending[8],0))/2);

        $maxkexen = floor((max($this->attaking[7]-$this->deffending[8],0))/2);



            //Dann wie viele Metallexen in den meißten fällen geklaut würden

        $rmexen = min($maxmexen, floor($this->mexen*0.1));





            //Wenn nich alle Schiffe, die für Metallexenlau bereitgestellt waren benutz werden, dürfen diezum Kristallexen klauen Benutzt werden

        if($rmexen != $maxmexen)

            $maxkexen += $maxmexen-$rmexen;



            //Kristallexen in den meißten fällen

        $rkexen = min($maxkexen, floor($this->kexen*0.1));



            // Wenn nich alle zum Kristallexen bereitgestellten Cleps benutzt wurden, rechnen wir nochmal Metallexen ob nich evtl mehr mit genommen werden können.

        if($rkexen != $maxkexen)

        {

            $maxmexen += $maxkexen-$rkexen;

            $rmexen = min($maxmexen, floor($this->mexen*0.1));

        }





            // Exen vom bestand abziehen und auch die benutzen Cleps "zerstören"

        $this->mexen -=$rmexen;

        $this->kexen -=$rkexen;

        $this->attaking[7] -= $rmexen+$rkexen;

        $this->geslostshipsatt[7] += $rmexen+$rkexen;



            //Für die Statistik, wie viele Exen insgesammt gestohlen wurden.

        $this->gesstolenexenm+=$this->stolenmexen = $rmexen;

        $this->gesstolenexenk+=$this->stolenkexen = $rkexen;
    }
}

$attacking[7] = 1000;
$mexen = 100;
$simulation = new GNSimu();
$simulation->compute(1); // Tick: Die Zeitrechnung in Galaxy-Network wird in Ticks angegeben. Ein Tick umfasst 15 Minuten realer Zeitrechnung. Wann der letzte Tick begonnen hat, kannst du ganz oben in deinem "Kontrollzentrum" ablesen.

print $simulation->stolenmexen;
