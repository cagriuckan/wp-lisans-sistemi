<?php
/**
 * Wordpress Tema ve Eklentiler İçin Uzak Sunucu Kontrollü Lisans Sistemi
 * @author Cagri Uckan
 * @package WordPress
 **/
// update_option('kan_license_data', '');
class Kan_Licensing_System {
	
	private $_server = 'http://localhost/wordpress/server.json'; // lisans serverı
	private $_option = 'kan_license_data'; // lisans datasının kaydedileceği tablo adı
	
	function __construct() {
		$this->init();
	}
	
	private function init() {
		add_action('init', array($this, 'result')); // tüm siteyi patlatır
		// add_action('admin_init', array($this, 'result')); // sadece admin panelini patlatır
		
		add_filter( 'switch_theme', array($this, 'clear_scheduled') ); // tema değiştirdiğinde görevi temizler
		add_action( 'kan_license_job', array($this, 'check') );	// görevin tanımı
		
		if ( !wp_next_scheduled( 'kan_license_job' ) ) {
			wp_schedule_event( time(), 'weekly', 'kan_license_job' ); // haftalık olarak tekrarlanan bir görev oluşturur
		}
	}
	
	/*
	* Görevi Temizle
	*/
	function clear_scheduled() {	
		wp_clear_scheduled_hook( 'kan_license_job' );	
	}
	
	/*
	* Site Domaini
	*/
	function site_domain() {
		$name = $_SERVER['HTTP_HOST'];
		$domain = preg_replace("(^https?://)", "", $name );
		if (substr($domain, 0, 4) == 'www.') {
			$domain = substr($domain, 4);
		}
		$domain = explode("/", $domain);
		return $domain[0];
	}
	
	/*
	* Lisans Kontrolü
	*/
	function check() {
		$current_domain = $this->site_domain();
		
		$args = array(
			'timeout' => '15',
			'sslverify' => false
		);
		
		$request = wp_remote_get( $this->_server, $args );
		
		if( is_wp_error( $request ) ) {
			return false;
		} 
		
		$body = wp_remote_retrieve_body( $request );
		$data = json_decode( $body );

		
		if( ! empty( $data ) ) {
			if (!$data->domains->$current_domain) {
				update_option($this->_option, 'invalid'); // geçersiz lisans
			} else {
				if ($data->domains->$current_domain->status ==  'expired')
					update_option($this->_option, 'expired'); // süresi dolan lisans
				elseif ($data->domains->$current_domain->status ==  'deactive')
					update_option($this->_option, 'deactive'); // etkisiz hale getirilen lisans
				else
					update_option($this->_option, 'active'); // geçerli lisans
			}
		}
	}
	
	/*
	* Sonuç
	*/
	function result() {
		$option = get_option($this->_option) ? get_option($this->_option) : '';
		
		if($option == 'active')
			return false;
		elseif($option == 'invalid')
			wp_die('Lisansınız geçersiz!');
		elseif($option == 'expired')
			wp_die('Lisansınızın süresi dolmuş.');
		elseif($option == 'deactive')
			wp_die('Lisansınız etkisiz hale getirilmiştir.');
	}
	
}