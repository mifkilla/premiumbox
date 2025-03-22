<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('pn_page_indicator')){
	function pn_page_indicator(){
		return preg_quote(apply_filters('pn_page_indicator' ,'num_page'));
	}
} 

if(!function_exists('get_pagenavi_calc')){
	function get_pagenavi_calc($limit,$get,$count){

		$current = intval($get); 
		if ($current) {
			$offset = ($current - 1) * $limit;
		} else {
			$offset = 0; 
			$current = 1;
		}
		
		$disable = $current * $limit; 
		if($count > 0 and $limit > 0){
			$countpage = ceil($count/$limit);
		} else {
			$countpage = 1;
		}

		if($countpage == $current){
			$next = $current;
		} else {
			$next = $current + 1; 
		}
		
		$prev = $current - 1; 
		if(!$prev){ $prev = 1; }
		if(!$countpage){ $countpage = 1; }

		$pagenavi = array();
		$pagenavi['limit'] = intval($limit);
		$pagenavi['current'] = intval($current);
		$pagenavi['disable'] = intval($disable);
		$pagenavi['countpage'] = intval($countpage);
		$pagenavi['next'] = intval($next);
		$pagenavi['prev'] = intval($prev);
		$pagenavi['offset'] = intval($offset);

		return $pagenavi;
	}
}

if(!function_exists('get_pagenavi_mini_calc')){
	function get_pagenavi_mini_calc($limit,$get){

		$current = intval($get); 
		if ($current) {
			$offset = ($current - 1) * $limit;
		} else {
			$offset = 0; 
			$current = 1;
		}
		
		$next = $current + 1; 
		$prev = $current - 1; 
		if(!$prev){ $prev = 1; }

		$pagenavi = array();
		$pagenavi['limit'] = intval($limit);
		$pagenavi['current'] = intval($current);
		$pagenavi['next'] = intval($next);
		$pagenavi['prev'] = intval($prev);
		$pagenavi['offset'] = intval($offset);

		return $pagenavi;
	}
}

if(!function_exists('get_pagenavi_mini')){
	function get_pagenavi_mini($pagenavi='', $url=''){
	global $or_site_url;

		$uri = $url;
		$page_indicator = pn_page_indicator();
		$uri = preg_replace('/[&?]{1}'. $page_indicator .'[=]{1}[0-9]{0,8}/','',$uri);
		
		$uri = pn_strip_input($uri);
		
		if(strstr($uri,'?')){
			$zn = '&';
			$explode_uri = explode('?',$uri);
			$uri = $explode_uri[0];
			$query = '?' . $explode_uri[1];
		} else {
			$zn = '?';
			$query = '';
		}
		$num_page = is_isset($pagenavi,'current');
		$prev = is_isset($pagenavi,'prev');
		$next = is_isset($pagenavi,'next');
		
		$pn = '<div class="pagenavi">';	

		if($num_page > 1){
			$pn .= '<a href="'. $uri . $query .'" rel="prev" data-page="1" class="first">'. __('First page','premium') .'</a>';
			$pn .= '<a href="'. $uri . $query . $zn . $page_indicator . '='. $prev .'" rel="prev" data-page="'. $prev .'" class="prev">&larr;</a>';
		}  
		
		$pn .= '<span>'. $num_page .'</span>';
		$pn .= '<a href="'. $uri . $query . $zn . $page_indicator .'='. $next .'" rel="next" data-page="'. $next .'" class="next">&rarr;</a>';
	  
		$pn .= '<div class="clear"></div></div>';
			return $pn;
	}
}

if(!function_exists('get_pagenavi')){
	function get_pagenavi($pagenavi='', $type='standart', $url=''){
	global $or_site_url;

		$page_indicator = pn_page_indicator();
	
		if($url){
			$uri = $url;
		} elseif(function_exists('get_site_url_ml')) {
			$uri = get_site_url_ml() . $_SERVER['REQUEST_URI'];
		} else {
			$uri = $or_site_url . $_SERVER['REQUEST_URI'];
		}

		if($type == 'standart'){
			$uri = preg_replace('/\/page\/[0-9]{0,8}/','',$uri);
		} else {
			$uri = preg_replace('/[&?]{1}'. $page_indicator .'[=]{1}[0-9]{0,8}/','',$uri);
		}
		
		$uri = pn_strip_input($uri);
		
		if(strstr($uri,'?')){
			$zn = '&';
			$explode_uri = explode('?',$uri);
			$uri = $explode_uri[0];
			$query = '?' . $explode_uri[1];
		} else {
			$zn = '?';
			$query = '';
		}
		
		if($type == 'standart'){ 
			$uri = rtrim($uri,'/').'/';
		}
		
		if(is_array($pagenavi)){
			$countpage = $pagenavi['countpage'];
			$num_page = $pagenavi['current'];
		} else {
			global $wp_query;
			$countpage = $wp_query->max_num_pages;
			
			if($type == 'standart'){
				$num_page = intval(get_query_var('paged'));
			} else {
				$num_page = intval(is_param_get($page_indicator));
			}		
		}	
		
		if(!$num_page){$num_page=1;}
		
		$pn = '';
		
		if($countpage > 1){
			
			$pn .= apply_filters('get_pagenavi_start', '<div class="pagenavi"><div class="pagenavi_ins">');

			if(is_admin()){
				$array = array();
				$array['first'] = __('First page','premium');
				$array['prev'] = '&larr;';
				$array['next'] = '&rarr;';
				$array['last'] = __('Last page','premium');
				$array['num'] = 2;
				$array['numleft'] = 1;
				$array['numright'] = 1;
				$array = apply_filters('get_pagenavi', $array, 'admin');
			} else {
				$array = array();
				$array['first'] = __('First page','premium');
				$array['prev'] = '&larr;';
				$array['next'] = '&rarr;';
				$array['last'] = __('Last page','premium');
				$array['num'] = 2;
				$array['numleft'] = 2;
				$array['numright'] = 2;
				$array = apply_filters('get_pagenavi', $array, 'site');			
			}
		
			$numleft = intval(is_isset($array,'numleft'));
			$numright = intval(is_isset($array,'numright'));
			$num = intval(is_isset($array,'num'));
		
			if($num_page > 1){
				if(isset($array['first']) and $array['first']){
					if($type == 'standart'){
						$pn .= '<a href="'. $uri . $query . '" data-page="1" rel="prev" class="first">'. $array['first'] .'</a>';
					} else {
						$pn .= '<a href="'. $uri . $query .'" data-page="1" rel="prev" class="first">'. $array['first'] .'</a>';
					}
				}
				if(isset($array['prev']) and $array['prev']){
					if($type == 'standart'){
						$dpage = $num_page-1;
						$pn .= '<a href="'. $uri .'page/'. $dpage .'/'. $query .'" rel="prev" data-page="'. $dpage .'" class="prev">'. $array['prev'] .'</a>';	
					} else {
						$dpage = $num_page-1;
						$pn .= '<a href="'. $uri . $query . $zn . $page_indicator .'='. $dpage .'" rel="prev" data-page="'. $dpage .'" class="prev">'. $array['prev'] .'</a>';
					}   
				}
			}  
	  
			$pagearr = array();
			$r=0;
			while($r++ < $numleft){
				$pagearr[] = $r;
			}
			
			$dc = ($num * 2) + 1;
			$r = $num_page - 1 - $num;
			$mr = $r + $dc;
			while($r++ < $mr){
				$pagearr[] = $r;
			}	
			
			$r = $countpage - $numright;
			while($r++ < $countpage){
				$pagearr[] = $r;
			}

			$pagearr = array_unique($pagearr);
			
			$pa = array();
			$lv = 0;
			$rel = 'prev';
			foreach($pagearr as $v){
				if($v > 0 and $v <= $countpage){
					if($lv and $lv != $v){
						$pn .= '<span>...</span>';
					}
					$lv = $v+1;				
					
					if($v == $num_page){
						$pn .= '<span class="current" data-page="'. $v .'">'. $v .'</span>';
						$rel = 'next';
					} elseif($v == 1){
						if($type == 'standart'){
							$pn .= '<a href="'. $uri . $query . '" rel="'. $rel .'" data-page="'. $v .'">'. $v .'</a>';
						} else {
							$pn .= '<a href="'. $uri . $query .'" rel="'. $rel .'" data-page="'. $v .'">'. $v .'</a>';
						}					
					} else {
						if($type == 'standart'){
							$pn .= '<a href="'. $uri .'page/'. $v .'/'. $query .'" rel="'. $rel .'" data-page="'. $v .'">'. $v .'</a>';	
						} else {
							$pn .= '<a href="'. $uri . $query . $zn . $page_indicator .'='. $v .'" rel="'. $rel .'" data-page="'. $v .'">'. $v .'</a>';
						}					
					}
				}
			}

			if($num_page != $countpage){
				if(isset($array['next']) and $array['next']){
					$dpage = $num_page+1;
					if($type == 'standart'){
						$pn .= '<a href="'. $uri .'page/'. $dpage .'/'. $query .'" data-page="'. $dpage .'" rel="next" class="next">'. $array['next'] .'</a>';	
					} else {
						$pn .= '<a href="'. $uri . $query . $zn . $page_indicator .'='. $dpage .'" data-page="'. $dpage .'" rel="next" class="next">'. $array['next'] .'</a>';
					} 				
				}
				if(isset($array['last']) and $array['last']){
					if($type == 'standart'){
						$pn .= '<a href="'. $uri .'page/'. $countpage .'/'. $query .'" data-page="'. $countpage .'" rel="next" class="last">'. $array['last'] .'</a>';	
					} else {
						$pn .= '<a href="'. $uri . $query . $zn . $page_indicator .'='. $countpage .'" data-page="'. $countpage .'" rel="next" class="last">'. $array['last'] .'</a>';
					} 				
				}						
			} 
	  
			$pn .= apply_filters('get_pagenavi_end', '<div class="clear"></div></div></div>');
		}
			return $pn;
	}
}

if(!function_exists('the_pagenavi')){
	function the_pagenavi($pagenavi='', $type='standart', $url=''){
		echo get_pagenavi($pagenavi, $type, $url);
	}
}