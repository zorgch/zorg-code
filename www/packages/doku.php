<?
   global $smarty;
   
   $smarty->register_block("doku", "doku_doku");      // {doku title="x" package="x"}..{/doku}
   $smarty->register_block("return", "doku_return");  // return type / was wird ausgegeben
   $smarty->register_block("param", "doku_param");    // {param name="parameter"}..{/param}
   $smarty->register_block("code", "doku_code");      // formatiert code (weisser block, courier)
   $smarty->register_block("entry", "doku_entry");
   
   
   function doku_doku ($params, $content, &$smarty, &$repeat) {
      $pack = "";
      if (isset($params[package])) {
         $pack = '<tr><td align="left"><b>Package: </b>'.$params[package].'<br /><br /></td></tr>';
      }
      
      $nav = "";
      if (isset($params[up]) || isset($params[home])) {
         $nav = '<tr><td align="left">';
         if (isset($params[home])) $nav .= '<a href="/?tpl='.$params[home].'">home</a> | ';
         if (isset($params[up])) $nav .= '<a href="/?tpl='.$params[up].'">up</a> | ';
         $nav = substr($nav, 0, -3);
         $nav .= '<br /><br /></td></tr>';
      }
      
      return  '<table width="100%">'.
                  $nav.
                  $pack.
                  '<tr><td align="left"><h2>'.$params[title].'</h3></td></tr>'.
                  '<tr><td align="left">'.$content.'</td></tr>'.
              '</table>';      
   }
   
   function doku_return ($params, $content, &$smarty, &$repeat) {
      return '<table width="100%"><tr>'.
               '<td width="15">&nbsp;</td>'.
               '<td align="left" valign="top" width="100"><b>Returns:</b></td>'.
               '<td align="left">'.$content.'</td>'.
             '</tr></table>';
   }
   
   function doku_param ($params, $content, &$smarty, &$repeat) {
      return '<table width="100%">'.
                  '<tr>'.
                     '<td width="15">&nbsp;</td>'.
                     '<td align="left" valign="top" width="100"><b>'.$params[name].'</b></td>'.
                     '<td align="left">'.$content.'</td>'.
                  '</tr>'.
              '</table>';
   }
   
   function doku_code ($params, $content, &$smarty, &$repeat) {
      return '<table cellpadding="4"><tr><td width="20">&nbsp;</td><td class="border" bgcolor="white" align="left"><code>'.$content.'</code></td></tr></table>';
   }
   
   
   function doku_entry ($params, $content, &$smarty, &$repeat) {
      if (isset($params[tpl])) {
         $params[title] = '<a href="/?tpl='.$params[tpl].'">'.$params[title].'</a>';
      }
      
      return '<table>'.
               '<tr>'.
                  '<td width="20">&nbsp;</td>'.
                  '<td align="left" valign="top">'.$params[title].' </td>'.
                  '<td align="left" valign="top"> - '.$content.' </td>'.
               '</tr>'.
             '</table>';
   }
?>