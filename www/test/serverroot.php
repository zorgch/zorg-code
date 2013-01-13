<?php
ini_set('display_errors',1);
error_reporting(E_ALL);	

echo '<img src="'.$_SERVER['DOCUMENT_ROOT'].'../data/userimages/582.jpg" alt="user" />
';
echo '<img src="/data/userimages/582.jpg" alt="user" />
';
echo '<img src="/userimages/582.jpg" alt="user" />
';
echo '<img src="'.$_SERVER['DOCUMENT_ROOT'].'images/pose.png" alt="pose" />
';
echo '<img src="/images/pose.png" alt="pose" />';
?>