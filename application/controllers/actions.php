<?php
class blognetworking_actions extends wv48fv_action {
	public function blognetworking_syncWPactionMeta($return)
	{
		$return['schedule'] = $this->data()->network['schedule'];
		return $return;
	}
	public function blognetworking_syncWPaction()
	{
		$network = $this->data()->network;
		$links = $this->get_links ( $network ['url'] );
		$blogs = $this->get_blogs ( $links );
		$blogs = $this->select_order ( $blogs, $this->data()->network);
		$blogs = array('posts'=>$blogs);
		$this->data()->writePost($blogs,'posts' );
	}
	private $in_write=false;
	public function blognetworking_writeWPfilter($data,$form) {
		if ($form=='default' && $data ['network']['url']!='') {
			do_action('blognetworking_sync');
		}
		return $data;
	}	
	private function get_blogs($links) {
		$blog = array ();
		/*
		$post_parent=$this->data()->getPostID('default');
		$tax_auth ='dc_'.$this->application()->slug.'_auth';
		$tax_cat ='dc_'.$this->application()->slug.'_cat';
		*/
		foreach ( $links as $link ) {
			if ($link ['xml_url'] != '') {
				$feed = fetch_feed ($link['xml_url']);
				if(!is_wp_error($feed))
				{
					$newblog = array ();
					$newblog['title']=$feed->get_title();
					$newblog['favicon']=$feed->get_favicon();
					$newblog['link']=$feed->get_permalink();
					$newblog['description']=$feed->get_description();
					$newblog ['items'] = array ();
					/*
					$postPath=array('default',$newblog['title']);
					$post = array(
						'post_title'=>$link['title'],
						'post_parent'=>$post_parent,
						'post_excerpt'=>$newblog['link'],
						'post_name'=>$this->data()->postName($postPath)
					);
					$sub_post_parent=$this->data()->writePost(null,$postPath,null,$post);
					*/
					foreach($feed->get_items() as $item)
					{
						$newitem = array ();
						$newitem ['title'] = $item->get_title();
						$newitem ['link'] = $item->get_permalink();
						$newitem ['pubdate'] = $item->get_date();
						$newitem ['author'] = $item->get_author()->name;
						$newblog ['items'] [] = $newitem;
						/*
						$postPath=array('default',$newblog['title'],$newitem['title']);
						$post = array(
							'post_title'=>$newitem['title'],
							'post_parent'=>$sub_post_parent,
							'post_content'=>$item->get_content(),
							'post_excerpt'=>$newitem ['link'],
							'post_date'=>$this->table()->date(strtotime($newitem ['pubdate'])),
							'post_date_gmt'=>get_gmt_from_date($this->table()->date(strtotime($newitem ['pubdate']))),
							'post_name'=>$this->data()->postName($postPath)
						);
						$sub_sub_post_parent = $this->data()->writePost(null,$postPath,null,$post);
						$tags = array();
						foreach($item->get_categories() as $term)
						{
							$slug = sanitize_title($term->term);
							$tags[]=$slug;
							wp_insert_term($term->term, $tax_cat, array(
    							'description'=> $term->term,
    							'slug' => $slug,
    							'parent'=> 0
  								)
  							);
  						}
  						$r=wp_set_post_terms($sub_sub_post_parent,$tags,$tax_cat,false);
						$tags = array();
						foreach($item->get_authors() as $term)
						{
							$slug = sanitize_title($term->name);
							$tags[]=$slug;
							wp_insert_term($term->name, $tax_auth, array(
    							'description'=> $term->name,
    							'slug' => $slug,
    							'parent'=> 0
  								)
  							);
  						}
  						$r=wp_set_post_terms($sub_sub_post_parent,$tags,$tax_auth,false);
						*/
					}
					$blog [$newblog['link']] = $newblog;
				}
			}
		}
		return $blog;
	}
	private function get_links($blogroll) {
		$f = new wv48fv_opml (  );
		$OPML = $f->get ($blogroll);
		$fixed = array ();
		if($OPML!==false)
		{
			foreach ( $OPML as $okey => $o ) {
				foreach ( ( array ) $o as $v ) {
					if (isset ( $v ['tag'] ) && $v ['tag'] == 'BODY') {
						foreach ( ( array ) $v as $v2 ) {
							if (isset ( $v2 ['tag'] ) && $v2 ['tag'] == 'OUTLINE') {
								$cat = $v2 ['attributes'] ['TITLE'];
								foreach ( ( array ) $v2 as $v3 ) {
									if (isset ( $v3 ['tag'] ) && $v3 ['tag'] == 'OUTLINE' && $v3 ['attributes'] ['TYPE'] == 'link') {
										$fixed_new = array ();
										$fixed_new ['title'] = $v3 ['attributes'] ['TEXT'];
										$fixed_new ['html_url'] = $v3 ['attributes'] ['HTMLURL'];
										$fixed_new ['xml_url'] = $v3 ['attributes'] ['XMLURL'];
										$fixed_new ['categories'] = $cat;
										if (array_key_exists ( $fixed_new ['title'], $fixed )) {
											$fixed [$fixed_new ['title']] ['categories'] .= ',' . $fixed_new ['categories'];
										} else {
											$fixed [$fixed_new ['title']] = $fixed_new;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $fixed;
	}
	public function select_order($blogs, $settings) {
		$posts = array ();
		foreach ( $blogs as $blog ) {
			$blog_det = $blog;
			unset ( $blog_det ['items'] );
			$cnt = 0;
			foreach ( $blog ['items'] as $item ) {
				$post = $item;
				$post ['blog'] = $blog_det;
				$posts [] = $post;
				$cnt ++;
				if ($cnt == $settings ['site_max']) {
					break;
				}
			}
		}
		usort ( $posts, array ($this, 'single_sort' ) );
		$newposts = array ();
		$cnt = 0;
		foreach ( $posts as $post ) {
			$cnt ++;
			$newposts [] = $post;
			if ($cnt == $settings ['net_max']) {
				break;
			}
		}
		$posts = $newposts;
		$newposts = null;
		return $posts;
	}
	public function single_sort($a, $b) {
		if (strtotime ( $a ['pubdate'] ) == strtotime ( $b ['pubdate'] )) {
			return 0;
		}
		return (strtotime ( $a ['pubdate'] ) > strtotime ( $b ['pubdate'] )) ? - 1 : 1;
	}
	public function initWPaction() {
		/*
		$args = array(
			'label'=>'Authors',
			'labels'=>array(),
			'public'=>true,
			'show_in_nav_menus'=>false,
			'show_tagcloud'=>false,
			'hierachical'=>false,
			'update_count_callback'=>false,
			'query_var'=>false,
			'rewrite'=>false,
			'capabilities'=>array()
		);
		register_taxonomy('dc_'.$this->application()->slug.'_auth','dc_'.$this->application()->slug,$args);
		$args = array(
			'label'=>'Categories',
			'labels'=>array(),
			'public'=>true,
			'show_in_nav_menus'=>false,
			'show_tagcloud'=>false,
			'hierachical'=>false,
			'update_count_callback'=>false,
			'query_var'=>false,
			'rewrite'=>false,
			'capabilities'=>array()
		);
		register_taxonomy('dc_'.$this->application()->slug.'_cat','dc_'.$this->application()->slug,$args);
		*/
		add_action ( 'showNetwork', array ($this, 'showNetwork' ) );
		blognetworking_widget::$application = $this->application ();
		register_widget ( 'blognetworking_widget' );
	}
	public function showNetwork($title = '') {
		if ($title == '') {
			$title = "Recent Community Posts";
		}
		$this->view->data = $this->data ( 'posts' )->posts;
		$this->view->title = $title;
		$this->view->_e ( $this->render_script ( 'front/widget.phtml' ) );
	}
}