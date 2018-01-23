<?php


require_once( __DIR__ .'/includes/main.inc.php');



if($_POST['submit'] && $_SESSION['user_id']) {
    $content = "Es gibt keine korrekten Antworten.\n"
                            ."Die Zukunft haengt von den Entscheidungen ab, die Sie und ich in den naechsten Stunde, in der naechsten Woche, im naechsten "
                            ."Jahrzehnt treffen werden.\n"
                            ."Die genaue Auswertung ist mit dem noetigen Geschick auf www.zorg.ch zu finden!";

    $header = "From: zorgsche kollektiv intelligenz <illuminatus@zorg.ch>\n";
    $sql = "SELECT * FROM user WHERE id = '$_SESSION[user_id]'";
    $result = $db->query($sql,__FILE__,__LINE__);
    $rs = $db->fetch($result);
    $sql = "INSERT into joinus (user_id, f1,f2,f3,f4,f5,f6,f7,f8,f9,f10,f11,f12,f13,f14, datum) VALUES
    ('".$_SESSION[user_id]."', '$_POST[f1]', '$_POST[f2]', '$_POST[f3]', '$_POST[f4]', '$_POST[f5]', 
    '$_POST[f6]', '$_POST[f7]', '$_POST[f8]', '$_POST[f9]', '$_POST[f10]', '$_POST[f11]', '$_POST[f12]',
     '$_POST[f13]', '$_POST[f14]', now())";

    $insert = $db->query($sql,__FILE__,__LINE__);
    
    mail($rs["email"],"Auswertung des Beitrittstests",$content, $header);
    
    Header ("Location: http://www.illuminatus.net");

}


function qlist($question, $answer, $input_name) {
  $html = "<table width=\"550\" class=\"border\"><tr>"
  ."<td>"
  ."<b>"
  .$question
  ."</b>"
  ."</td></tr>";
  foreach($answer as $key) {
    $html .= "<tr><td>"
    .$key
    ."</td></tr>";
  }
  $html .= "<tr><td>"
  ."<input type=\"text\" name=\"".$input_name."\" size=\"30\" class=\"text\">"
  ."</td></tr></table>";
  return $html;
}

function qradios ($question, $answer, $input_name) {
  $html = "<table width=\"550\" class=\"border\"><tr>"
  ."<td colspan=\"2\">"
  ."<b>"
  .$question
  ."</b>"
  ."</td></tr>";
  foreach($answer as $key) {
    $html .= "<tr><td style=\"width: 50px;\">"
    ."<input type=\"radio\" name=\"".$input_name."\" value=\"".$key."\"/>"
    ."</td><td style=\"width: 500px;\">".$key."</td></tr>";
  }
  $html .= "</table>";
  return $html;

}



$q1 = "1) F&uuml;ge den N&auml;chsten Begriff bei:";

$a1 = array("gehen", "auf einem Pferd reiten", "mit einem Jet fliegen");



$q2 = "2) Eine gewisse Arbeit kann entweder von einem Menschen oder von einer Maschine ausgef&uuml;hrt werden. Wir sollten";

$a2 = array("den Menschen anstellen \" weil der Teufel m&uuml;ssige H&auml;nde erfunden hat \"",

            "den Menschen anstellen weil sonst sie oder er sich langweilen k&ouml;nnte",

            "den Menschen anstellen weil es keinen andern Weg zur Organisation der Gesellschaft gibt, ausser dass man die meisten Leute gegen Entl&ouml;hnung arbeiten l&auml;sst.",

            "die Maschine anstellen, weil die Technik keine andere Funktion hat, als den Menschen von der Plackerei zu befreien."

            );



$q3 = "3) F&uuml;ge den N&auml;chsten Begriff bei:";

$a3 = array("Jagen und Sammeln", "Landwirtschaft", "Industrie-Handel");



$q4 = "4) Eine magische Maschine hat zwei Kn&ouml;pfe, mit deren Hilfe die Gleichheit unter den Menschen geschaffen wird. Du dr&uuml;ckst";

$a4 = array("den Knopf, der alle gleich arm macht", "den Knopf, der alle gleich reich macht");



$q5 = "5) F&uuml;ge den N&auml;chsten Begriff bei:";

$a5 = array("Steintafel", "Tinte und Papier", "globales Fernsehen");



$q6 = "6) Arbeit gegen Entl&ouml;hnung";

$a6 = array("hat es immer gegeben und wird es immer geben",

            "ist von Gott auferlegt",

            "war im grossen Rahmen nicht &uuml;blich, ehe die Aufteilung des Grundbesitzes w&auml;hrend der vergangenen dreihundert Jahre die Leibeigenen von Grund und Boden vertrieb",

            "wird in den n&auml;chsten hundert Jahren &uuml;berholt sein",

            "wird in den n&auml;chsten zehn Jahren &uuml;berholt sein"

            );



$q7 = "7) F&uuml;ge den N&auml;chsten Begriff bei:";

$a7 = array("Zahlen", "Kalender", "wissenschaftliche Gesetze");



$q8 = "8) Es gibt heute mehr Wissenschaftler als in der gesamten bisherigen Geschichte. Laut Toffler - und anderen - bedeutet dies, dass wir im Verlauf der n&auml;chsten dreissig Jahre mehr Ver&auml;nderungen erleben werden, als dies in der gesamten bisherigen Geschichte der Fall war. Wir sollten deshalb:";

$a8 = array("die H&auml;lfte - oder mehr - dieser Wissenschaftler zwingen, Schuhverk&auml;ufer oder Gem&uuml;seh&auml;ndler zu werden, damit sich die Dinge nicht allzu schnell ver&auml;ndern",

            "einen Regierungsausschuss bilden, der die gesamte wissenschaftliche Forschung &uuml;berwacht und damit die Sache noch mehr verz&ouml;gert",

            "lernen, die allgemeine Intelligenz zu steigern, um mit der Ver&auml;nderung fertig zu werden"

           );



$q9 = "9) Der beste Weg, um nach H&ouml;herer Intelligenz zu suchen, besteht im";

$a9 = array("finden der richtigen Religion",

            "unterst&uuml;tzen des SETI Projekt's",

            "erforschen von UFO's",

            "erforschen unseres Nervensystems",

            "bauen eines Sternenschiffs; an Ort und Stelle nachsehen"

           );



$q10 = "10) Die Zeitschrifft \"Time\" meint, dass wir \"innerhalb von 15 Jahren\" &uuml;ber Techniken verf&uuml;gen werden, die uns erlauben, unser Nervensystem auf immerw&auml;hrende Wonne umzustellen";

$a10 = array("das ist f&uuml;rchterlich; der Hedonismus wird uns alle zerst&ouml;ren",

             "das ist sch&ouml;n; wozu sonst sollte die Forschung auf dem Gebiet der Neurologie gut sein ? ",

             "wir verf&uuml;gen &uuml;ber diese Techniken seit 1960, aber Einkerkerung und fortw&auml;hrende Bel&auml;stigung haben jene zum Schweigen gebracht, die davon wussten"

            );



$q11 = "11) Wem glaubst du:";

$a11 = array("konservativen Stellen, die sagen, dass die Lebensspanne nicht weiter verl&auml;ngert werden kann, als dies heutzutage m&ouml;glich ist ?",

             "dem Gerontologen Paul Segall, der sagt, dass wir eine Lebensspanne von 500 Jahren erreichen k&ouml;nnen ? ",

             "dem Biologen Johan Bjorkstein, der sagt, dass wir 800 Jahre alt werden k&ouml;nnen ?",

             "Dr. med. Robert Phedra, der sagt, dass wir 1000 Jahre alt werden k&ouml;nnen ?",

             "dem Physiker R.C.W. Ettinger, der sagt, dass wir Unsterblichkeit erreichen k&ouml;nnen ?"

            );



$q12 = "12) Die heute g&uuml;ltigen Ansichten werden in folgendem Jahr f&uuml;r altmodisch und etwas ungenau erachtet:";

$a12 = array("2010","2030","2060","2100","2500","3000");



$q13 = "13) Die heute g&uuml;ltigen Ansichten werden in folgendem Jahr als idiotischer Aberglaube erachtet:";

$a13 = array("2005","2010","2050","2150","3000");



$q14 = "14) F&uuml;ge den N&auml;chsten Begriff bei:";

$a14 = array("nicht Euklidische Geometrie", "nicht Newtonsche Physik", "nicht Aristotelische Logik");



if($_SESSION['user_id']) {

  echo(

    head()
	.menu("zorg")
	.menu("user")

    ."<br /><b>zooomclan Beitritts Test</b>"



    ."<br /><br />"

    ."Der folgende Test, von Illuminati International in Zusammenarbeit mit zooomclan.org, misst die pers&ouml;ndliche Bef&auml;higung um dem zooomclan beizutretten."

    ."<br />"

    ."Das Resultat wird Ihnen per E-Mail zu gestellt!"

    ."<br /><br />"

    ."Lese jede Frage genau durch und denke dar&uuml;ber nach was du angibst, es k&ouml;nnte dein weiteres Leben vollst&auml;ndig umkrempeln"

    ."<br /><br />"

    ."<form action='$_SERVER[PHP_SELF]' method='POST'>"

    ."<center>"

    ."<table>"

    ."<tr><td align=\"center\">"

    .qlist($q1,$a1,"f1")

    ."<br />"

    .qradios($q2,$a2,"f2")

    ."<br />"

    .qlist($q3,$a3,"f3")

    ."<br />"

    .qradios($q4,$a4,"f4")

    ."<br />"

    .qlist($q5,$a5,"f5")

    ."<br />"

    .qradios($q6,$a6,"f6")

    ."<br />"

    .qlist($q7,$a7,"f7")

    ."<br />"

    .qradios($q8,$a8,"f8")

    ."<br />"

    .qradios($q9,$a9,"f9")

    ."<br />"

    .qradios($q10,$a10,"f10")

    ."<br />"

    .qradios($q11,$a11,"f11")

    ."<br />"

    .qradios($q12,$a12,"f12")

    ."<br />"

    .qradios($q13,$a13,"f13")

    ."<br />"

    .qlist($q14,$a14,"f14")

    ."<br /><br />"

    ."<input type=\"text\" name=\"name\" class=\"text\"/> Ihr vollst&auml;ndiger Name (wir wissen alles &uuml;ber sie!)"

    ."<br />"

    ."<input type=\"submit\" name=\"submit\" value=\"abschicken\" class=\"button\"/>"

    ."</td>"

    ."</tr>"

    ."</table>"

    ."</form>"

    ."<br /><b>"

    ."Die Bedingungen um der geheimen und welt&auml;ltesten Verschw&ouml;rung der Illuminaten bei zu tretten:"
    ."</b><br /><br />"
    ."Falls dein I.Q. gr&ouml;sser als 150 ist und du &uuml;ber 3125 US-Dollar (plus Versandkosten) verf&uuml;gst, k&ouml;nntest du f&uuml;r eine trilaterale A.I.S.B-Mitgliedschaft geeignet sein. Falls du dich zu eignen glaubst, so stecke obigen Betrag in eine Zigarettenschachtel und vergrabe diese im Hinterhof. Einer unserer Untergrund-Agenten wird alsbald mit dir in Kontakt tretten."
    ."<br /><br /><b>"
    ."Wir fordern dich heraus!"
    ."</b><br /><br />"
    ."Sage niemandem: Unf&auml;lle stehen in einem seltsamen Zusammenhang zu Leuten, die zuviel &uuml;ber die bayrischen Illuminaten sprechen."
    ."<br /><br />"
    ."</center>"
    .foot()
  );
} else {
  echo(
    head()
    ."Please login!"
    .foot()
  );
}



?>