<?php
	require_once( __DIR__ .'/../includes/smarty.inc.php');

	/**
	 * da php keine pointers kennt, m�ssen alle MenuTree-objekte mit ihrer id �ber dieses
	 * array aufgerufen werden! beim erzeugen von objekten werden sie automatisch im array
	 * gespeichert. also bitte keine zuweisungen von solchen objekten machen, sondern nur 
	 * die id verwenden.
	 */
	$menu_tabs = array();

	
	// root:
	new MenuTree(0, -1);
	
	
	/**
	 * menu_stack is used to build the menu structure. top element is root
	 */
	$menu_stack = array(0);
	
	
	$active_tab = 0;
	
	class MenuTree {
		var $id;
		var $name;
		var $link;
		var $parent;
		var $group;
		var $subtrees = array();
	
		
		function __construct ($id, $parent, $group="all", $tpl="", $link="", $param="") {
			global $menu_tabs;
			
			$this->id = $id;
			$this->subtrees = array();
			
			if ($group != "member" && $group != "user" && $group != "guest") $group = "all";
			$this->group = $group;
			
			$this->parent = $parent;
			if ($parent >= 0) $menu_tabs[$parent]->subtrees[] = $id;
			
			if ($tpl) {
				$this->link = "/?tpl=$tpl";
			}else{
				$this->link = $link;
			}
			
			if ($param) $this->link .= "&$param";
			
			$menu_tabs[$id] = $this;
		}
		
		function level () {
			global $menu_tabs;
			
			if ($this->parent < 0) return 0;
			else {
				return $menu_tabs[$this->parent]->level() + 1;
			}
		}
		
		function draw ($depth=1, $height=0, $sel=0) {
			global $menu_tabs, $user;
			
			if (!$height) $height = $this->level();
			if ($height < 1) $height = 1;
			
			
			$ret = "";
			if (!sizeof($this->subtrees)) --$depth;
			
			if ($this->parent >= 0) $ret .= $menu_tabs[$this->parent]->draw($depth+1, $height, $this->id);
			
			if (sizeof($this->subtrees)) {
				$ret .= '<div align="center" class="tabs'.$height.$depth.'">';
				foreach ($this->subtrees as $it) {
					if($menu_tabs[$it]->group == "all"
						|| $menu_tabs[$it]->group == "guest" && $_SESSION['user_id'] == ''
						|| $menu_tabs[$it]->group == "user" && $_SESSION['user_id'] != ''
						|| $menu_tabs[$it]->group == "member" && $user->typ == USER_MEMBER
					) { 
		
						if ($sel == $it) {
							$class = 'class="selected"';
						}else{
							$class = "";
						}
						$ret .= '<a '.$class.' href="'.$menu_tabs[$it]->link.'">'.$menu_tabs[$it]->name.'</a>';
					}
				}
				$ret .= '</div>';
			}
			
			return $ret;
		}
		
		function print_subtrees () {
			$ret = "subtrees of $this->id / anz: ".sizeof($this->subtrees)." > ";
			foreach ($this->subtrees as $it) {
				$ret .= "$it, ";
			}
			return "$ret <br>";
		}
		
	}
	
	
	

	/**
	 * Params:
	 * id			kannst irgend eine ausw�hlen, darf aber keine duplikate geben / f�r tab-auswahl im file n�tig / 
	 *				0 darf nicht verwendet werden. 
	 * group		nur angezeigt, wenn recht vorhanden. m�gl. werte: [all, user, member, guest], default: all
	 * tpl		[nur wenn url nicht gesetzt] template, das geladen werden soll. 
	 * url		[nur wenn tpl nicht gesetzt] url, die geladen werden soll (nur in der untersten stufe n�tig
	 *
	 */ 

	function smarty_mtab ($params, $content, &$smarty, &$repeat) {
		global $active_tab, $menu_stack, $menu_tabs;
		
		if ($repeat) {  // opening tag
			$parent = $menu_stack[sizeof($menu_stack)-1];
			new MenuTree($params['id'], $parent, $params['group'], $params['tpl'], $params['url'], $params['param']);
			
			array_push($menu_stack, $params[id]);
			
			if ($params['id'] == $active_tab) {
				$active_tab = $params[id];
			}
			
			if ($params['default'] && $active_tab == $menu_tabs[$parent]->id) {
				$active_tab = $params[id];
			}
		}else{   // closing tag
			$content = htmlentities($content, ENT_QUOTES);
			if (preg_replace("/\s/", "", $content) == "") $content = "--";
			
			$obj = array_pop($menu_stack);
			$menu_tabs[$obj]->name = $content;
		}
	}
	
	
	
	/**
	 * Params:
	 * active		aktiver Menu-Tab
	 */
	 
	function smarty_menu_old ($params, $content, &$smarty, &$repeat) {
		global $active_tab, $menu_stack, $menu_tabs;
			
		if ($repeat) {  // opening tag
			
			if ($params[active]) {
				$active_tab = $params[active];
			}
		}else{  // closing tag
			if ($menu_tabs[$active_tab]) {
				return $menu_tabs[$active_tab]->draw();
			}else{
				return $menu_tabs[0]->draw();
			}
		}
	}
	
	
	$smarty->register_block("menu_old", "smarty_menu_old");
	$smarty->register_block("mtab", "smarty_mtab");
