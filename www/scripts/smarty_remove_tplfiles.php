<?php
echo '<!doctype html><html><head><title>Deleting Smarty templates_c files</title><style>body{font-family:Helvetica,Arial,sans-serif;}a{text-decoratio$

if(isset($_GET['pw']) && $_GET['pw'] == 'schmelzigel')
{
        $i = 0;
        $del = 0;
        $files = glob('/var/data/smartylib/templates_c/*'); // get all file names
        foreach ($files as $file) // iterate files
        {
                $i++;
                echo '<p><strong>Found:</strong> ('.$i.') <span class="4">'.$file.'</span>';
                if (is_file($file))
                   unlink($file); // delete file
                   $del++;
                   echo ' => <span class="3">deleted!<span></p>';
        }
        echo '<h2><span class="4">Total files: '.$i.'</span> <span class="3">=> deleted: '.$del.'</span></h2>';
} else {
        echo '<code>tell me the magic word</code>';
}
echo '</body></html>'; // Close HTML