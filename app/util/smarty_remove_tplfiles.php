<?php
require_once __DIR__.'/../../public/includes/config.inc.php';
require_once INCLUDES_DIR.'smarty.inc.php';

echo '<!doctype html><html><head><title>Deleting Smarty templates_c files</title><style>body{font-family:Helvetica,Arial,sans-serif;}</style></head><body>';

if ($user->is_loggedin() && !empty(USER_SPECIAL) && $user->typ >= USER_SPECIAL)
{
        $i = 0;
        $del = 0;
        $files = glob('SMARTY_COMPILE/*'); // get all file names
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
        echo '<code>tusch du mol zerscht iilogge gell</code>';
}
echo '</body></html>'; // Close HTML
