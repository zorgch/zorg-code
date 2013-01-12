<?php

//=============================================================================
// Calculate sunrise, sunset, and twilights for the current day 
// for a given latitude & longitude.  Not accurate for very high 
// latitudes (> 65 deg) for certain times of year.
//
// Author: Marc A. Murison
//         U.S. Naval Observatory
//         Washington, DC 20392
//         murison@usno.navy.mil
//         http://arnold.usno.navy.mil/murison/
//=============================================================================

//-----------------------------------------------------------------------------
// main function to calculate twilight and rise/set times for the Sun
// lat and lon must be decimal degrees
//-----------------------------------------------------------------------------
function SunRiseSet( $lat /*decimal degrees North*/, 
                     $lon /*decimal degrees west longitude*/ ) 
{
    global $torad, $tzone, $ratio;

    //constants    
    $ratio  = 1.00273790935;    //sidereal days per solar day
    $torad  = M_PI/180.0;       //convert degrees to radians
    $zset   = 90.83327*$torad;  //rise/set zenith angle
    $zcivil = 96*$torad;        //civil twilight zenith angle
    $zastro = 108*$torad;       //astronomical twilight zenith angle

    //determine the local time zone
    $tzone   = round($lon/15.0);
    $today   = getdate(time()); //use PHP function to get current local date
    $day     = $today['mday'];  //day of month
    $month   = $today['mon'];   //month number
    $year    = $today['year'];
//  $weekday = mcal_day_of_week($year,$month,$day);   doesn't seem to work
    $utime   = mktime (1,1,1,$month,$day,$year);
    $weekday = date('w',$utime);
    if( $month > 3 && $month < 11 ) { //adjust tzone for daylight savings
        if( $month == 4 and $day < 7 ) {    //check for first Sunday in April
//          $day1   = mcal_day_of_week($year,$month,1);
            $utime  = mktime (1,1,1,$month,1,$year);
            $day1   = date('w',$utime);
            $sunday = 1 + (7-$day1);        //day of month of 1st Sunday
            if( $day >= $sunday ) {
                $tzone -= 1;
            }
        } else if( $month == 10 and $day > 24 ) {    //check for last Sunday in October
//          $day31  = mcal_day_of_week($year,$month,31);
            $utime  = mktime (1,1,1,$month,31,$year);
            $day31  = date('w',$utime);
            $sunday = 31 - $day31;          //day of month of last Sunday
            if( $day >= $sunday ) {
                $tzone -= 1;
            }
        } else {
            $tzone -= 1;
        }
    }

    //now that time zone is determined, get UT date
    $today   = getdate(time()+$tzone*3600); //use PHP function to get current UT date
    $day     = $today['mday'];              //day of month
    $month   = $today['mon'];               //month number
    $year    = $today['year'];
    
    //day numbers
    $jd     = gregoriantojd($month,$day,$year)-0.5; //JD at 0h UT
    $ndays  = $jd - 2451545.0;  //number of days from J2000.0 = Jan. 1.5 UT 2000
    $T      = $ndays/36525.0;   //Julian centuries from J2000.0
    
    //calculate Greenwich mean sideral time at 0h in decimal hours of time
    $GMST0  = 24110.54841 + (8640184.812866 + (0.093104 - 0.0000062*$T)*$T)*$T;
    $GMST0  = fmod($GMST0/3600.0,24);
    
    //local parameters
//  $refraction = 34.0/60.0;                    //average refraction at horizon is 34 arcmin
//  $zset   = (90 + $sd + $refraction)*$torad;  //sunrise/set zenith angle

    //Loop to calculate the rise/set times.  For latitudes far from the Arctic 
    //Circle, just one iteration is usually sufficient to provide accuracies 
    //under a minute.  The following loop always does at least two iterations.
    $t_rise   = 6;          //initial guess for rise times
    $t_set    = 18;         //initial guess for set times
    $times[0] = $t_rise;    //sunrise
    $times[1] = $t_set;     //sunset
    $times[2] = $t_rise;    //civil twilight -- rise
    $times[3] = $t_set;     //civil twilight -- set
    $times[4] = $t_rise;    //astronomical twilight -- rise
    $times[5] = $t_set;     //astronomical twilight -- set
    $zvals[0] = $zset;    $zvals[1] = $zset;
    $zvals[2] = $zcivil;  $zvals[3] = $zcivil;
    $zvals[4] = $zastro;  $zvals[5] = $zastro;
    $signh[0] = -1;  $signh[1] = +1;
    $signh[2] = -1;  $signh[3] = +1;
    $signh[4] = -1;  $signh[5] = +1;
    $terror   = 0.25/60;    //tolerance in decimal hours
    $countmax = 10;         //max number of iterations
    for( $i=0; $i < 6; ++$i ) {
        $tnew = $times[$i];  $told = 0;  $count = 0;
        while( abs($tnew - $told) > $terror && $count < $countmax ) {
            $told = $tnew;
            $tnew = improvelocaltime( $told, $zvals[$i], $GMST0, $ndays, 
                                      $lat, $lon, $signh[$i], $azimuths[$i] );
            ++$count;
        }
        $times[$i]     = $tnew;
        $azimuths[$i] /= $torad;
    }
    
    //calculate the local meridian transit time of the Sun
    $ttrans = Sun_transit_time( $lon, $ndays );
    
    //calculate the altitude of the Sun at transit
    SunEqCoords( $ttrans+$tzone, $ndays, $alpha /*decimal hours*/, $dec /*radians*/ );
    $alt = 90 - $lat + $dec/$torad;

    //output an array of values that can be used $vals[$i]["t"] for times
    //and $vals[$i]["a"] for corresponding azimuths (in the case of rise
    //and set times) or altitude (in the case of meridian transit), with
    //the order being [astro twilight rise, civil twilight rise, sunrise,
    //meridian transit, set, civil twilight set, astro twilight set] as
    //$i runs from 0-6
    for( $i=0; $i < 7; ++$i ) {
        if( $i < 3 ) {
            $vals[$i]["t"] = $times[$i];
            $vals[$i]["a"] = $azimuths[$i];
        } else if( $i == 3 ) {
            $vals[$i]["t"] = $ttrans;
            $vals[$i]["a"] = $alt;
        } else {
            $vals[$i]["t"] = $times[$i-1];
            $vals[$i]["a"] = $azimuths[$i-1];
        }
    }
//  print_r($vals);
    return $vals;
}

//-----------------------------------------------------------------------------
//Print a string containing SunRiseSet() output that looks like this:
//astro az civil az  rise az trans alt  set  az civil  az astro  az
//03:59 43 05:19 58 05:50 63 13:06 72 20:20 298 20:51 303 22:12 318
//
//A typical call might look like this: 
//echo printRiseSets( SunRiseSet( $lat_DC, $lon_DC ) );
//-----------------------------------------------------------------------------
function printRiseSets( $A ) {
    $style   = "<span style=\"font-family: monospace; font-size: 11;\">";
    $spanstr = "<span style=\"color:#999999\">";
    $heading = "astro&nbsp;".$spanstr."az</span>&nbsp;".
               "civil&nbsp;".$spanstr."az</span>&nbsp;&nbsp;".
               "rise&nbsp;".$spanstr."az</span>&nbsp;".
               "trans&nbsp;".$spanstr."alt</span>&nbsp;&nbsp;".
               "set&nbsp;&nbsp;".$spanstr."az</span>&nbsp;".
               "civil&nbsp;&nbsp;".$spanstr."az</span>&nbsp;".
               "astro&nbsp;&nbsp;".$spanstr."az</span><br>";
    $times = "";
    for( $i=0; $i < 7; ++$i ) {
        $times = $times.printtime($A[$i]["t"],false)."&nbsp;".$spanstr.
                 round($A[$i]["a"])."</span>&nbsp;";
    }
    $nargs = func_num_args();
    $args  = func_get_args();
    if( $nargs > 1 && $args[1] == 'skipheader' )
        $msg = $style.$times;
    else
        $msg = $style.$heading.$times;
    $msg = $msg."</span>";

    return $msg;
}

//-----------------------------------------------------------------------------
// given an initial guess for the local time t (decimal hours) corresponding 
// to a sunrise or sunset, calculate the rise or set time (decimal hours) 
// based on that initial guess
//
// the sun's azimuth is put into $az, in radians
//-----------------------------------------------------------------------------
function improvelocaltime( $t /*decimal hours*/, $z /*radians*/, 
                           $GMST0 /*decimal hours*/, $ndays, 
                           $lat /*decimal degrees*/, $lon /*decimal degrees*/, 
                           $signh /*+1 for set, -1 for rise*/,
                           &$az /* radians */ ) {

    global $torad, $tzone;
    
    //calculate equatorial coords for Sun
    //RA and DEC are returned with decimal hours and radians
    SunEqCoords( $t+$tzone, $ndays, $alpha /*decimal hours*/, $dec /*radians*/ );
    
    //calculate rise/set hour angle (decimal hours) and azimuth (radians)
    hour_az( $z, $dec, $lat*$torad, $signh, $h, $az );
    
    //get local time in decimal hours from hour angle
    $tlocal = getlocaltime( $h, $alpha, $GMST0, $lon/15 );

    return $tlocal;
}

//-----------------------------------------------------------------------------
// calculate equatorial coordinates of the Sun for UT time t on 
// date ndays after J2000.0
//-----------------------------------------------------------------------------
function SunEqCoords( $t /*decimal hours UT*/, $ndays, 
                      &$alpha /*decimal hours*/, &$dec /*radians*/ ) {
    global $torad;
    
    $dt     = $ndays + $t/24;
    $L      = 280.460 + 0.9856474*$dt;      //solar mean longitude in deg
    $M      = 357.528 + 0.9856003*$dt;      //solar mean anomaly in deg
    $L      = fmod($L,360.0);
    $M      = fmod($M,360.0);
    $eL     = $L + 1.915*sin($M*$torad) 
                 + 0.020*sin(2*$M*$torad);  //solar ecliptic longitude in deg
    if( $eL < 0 )  $eL += 360;
    $eps    = 23.439 - 0.0000004*$dt;       //obliquity of the ecliptic (deg)
//  $R      = 1.00014 - 0.01671*cos($M*$torad) - 0.00014*cos(2*$M*$torad);  //distance of Sun in AU
//  $sd     = 0.2666/$R;                    //apparent solar radius in degrees
    
    //RA and DEC of Sun at time t
    $dec    = asin( sin($eL*$torad)*sin($eps*$torad) ); //radians
    $ca     = cos($eL*$torad)/cos($dec);
    $sa     = sin($eL*$torad)*cos($eps*$torad)/cos($dec);
    $alpha  = angle($ca,$sa)/$torad/15;                 //decimal hours
}

//-----------------------------------------------------------------------------
// calculate local transit time in decimal hours of the Sun on 
// date ndays after J2000.0
//-----------------------------------------------------------------------------
function Sun_transit_time( $lon /*decimal degrees*/, $ndays ) {

    global $tzone;

    $terror   = 1.0/3600;   //tolerance in decimal hours
    $countmax = 5;          //max number of iterations
    $tnew     = 12;         //initial guess (local noon)
    $told     = 0;
    $count    = 0;
    while( abs($tnew - $told) > $terror && $count < $countmax ) {
        $told = $tnew;
        //get the ecliptic coords of the Sun
        SunEqCoords( $told+$tzone, $ndays, $alpha, $dec );
        //calculate the local equation of time (hour angle of true Sun at local noon)
        $L    = 280.460 + 0.9856474*($ndays+0.5); //solar mean longitude in deg
        $L    = fmod($L,360);
        $EOT  = $L/15-$alpha;
        //calculate the local transit time
        $tnew = 12 + ($lon/15-$tzone) - $EOT;
        ++$count;
    }
    if( $tnew < 0 ) $tnew += 24;
    
    return $tnew;
}
    
//-----------------------------------------------------------------------------
// return hour angle (decimal hours) and azimuth (radians)
// input args z, dec, and lat must all be in radians
// signh = {-1 for rise, +1 for set}
//-----------------------------------------------------------------------------
function hour_az( $z, $dec, $lat, $signh, &$h, &$az ) {
    global $torad;
    $sz   = sin($z);
    $cz   = cos($z);
    $sd   = sin($dec);
    $cd   = cos($dec);
    $sphi = sin($lat);
    $cphi = cos($lat);
    $ch   = ($cz-$sd*$sphi)/($cd*$cphi);    //cos(h*)
    $h    = acos($ch);                      //h* in radians
    $saz  = -$cd*sin($h)/$sz*$signh;
    $caz  = ($sd-$sphi*$cz)/($cphi*$sz);
    $az   = angle($caz,$saz);
    $h    = $signh*$h/$torad/15;            //h = sign(h)*(h*) in decimal hours
}

//-----------------------------------------------------------------------------
// calculate local time in decimal hours from hour angle 
// (all args in decimal hours)
//-----------------------------------------------------------------------------
function getlocaltime( $h, $alpha, $GMST0, $lon ) {

    global $ratio, $tzone;

    $LST = $h + $alpha;
    if( $LST < 0 ) $LST += 24;
    $dt = ($LST + $lon - $GMST0)/$ratio;    //time elapsed since UT midnight

    //if the time zone correction to get local time sends the latter
    //negative, then the elapsed time since UT midnight is actually 
    //dt + 24 sidereal hours
    $localt = $dt - $tzone;
    if( $localt < 0 ) $localt += 24/$ratio;

    return $localt;
}

//-----------------------------------------------------------------------------
// given sin and cos, return the angle in the correct quadrant, in radians
//-----------------------------------------------------------------------------
function angle( $c, $s ) {
    if( $s >= 0.0 ) {
        if( $c < 0.0 )
            return M_PI - asin($s);
        else
            return asin($s);
    } else {
        if( $c < 0.0 )
            return M_PI - asin($s);
        else
            return 2.0*M_PI + asin($s);
    }
}

//-----------------------------------------------------------------------------
// given t in decimal hours, return a string in the format "hh:mm:ss"
//-----------------------------------------------------------------------------
function printtime( $t /*decimal hours*/, $printseconds=true ) {
    $T = abs($t);
    $hh = floor($T);
    $mm = floor(($T-$hh)*60);
    $ss = round(($T-$hh-$mm/60.0)*3600);
    if( $ss==60 ) {
        ++$mm;
        $ss = 0;
    }
    if( !$printseconds ) {
        if( $ss >= 30 ) {
            ++$mm;
        }
    }
    if( $mm==60 ) {
        ++$hh;
        $mm = 0;
    }
    if( $hh >= 24 ) $hh -= 24;
    $s = $ss < 10 ? "0$ss" : "$ss";
    $m = $mm < 10 ? "0$mm" : "$mm";
    $h = $hh < 10 ? "0$hh" : "$hh";
    if( $t < 0 ) $h = "-".$h;
    if( $printseconds )
        return "$h:$m:$s";
    else 
        return "$h:$m";
}

?>