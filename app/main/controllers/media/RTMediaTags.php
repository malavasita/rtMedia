<?php

/**
 * Description of RTMediaTags
 *
 *
 */
class RTMediaTags {
	/**
	 *
	 * @var object a new instance of the getid3 class
	 */
	private static $_id3;

	/**
	 *
	 * @var the file to analyze
	 */
	private $file;

	/**
	 *
	 * @var object holds a copy of the variable $_id3
	 */
	private $id3;

	/**
	 *
	 *
	 * @var array key and value of analyzed file
	 */
	private $data = null;

	/**
	 * @var array $duration_info
	 */
	private $duration_info = array( 'duration' );

	/**
	 * Define media tags
	 *
	 * @var array $tags
	 */
	private $tags = array(
		'title',
		'artist',
		'album',
		'year',
		'genre',
		'comment',
		'track',
		'track_total',
		'attached_picture',
		'image',
	);

	/**
	 * Define media tags
	 *
	 * @var array $readonly_tags
	 */
	private $readonly_tags = array( 'track_total', 'attached_picture', 'image' );

	/**
	 * Initialises the __construct.
	 */
	public function __construct( $file ){

		$this->file = $file;
		$this->id3  = self::id3();
	}

	/**
	 * Update file path
	 *
	 * @param $file
	 */
	public function update_filepath( $file ){

		$this->file = $file;
	}

	/**
	 *
	 * Writes data inside  the files after manipulation, mainly mp3 files.
	 *
	 * @param null
	 *
	 * @return boolean
	 */
	public function save(){

		include_once( trailingslashit( RTMEDIA_PATH ) . 'lib/getid3/write.php' );

		$tagwriter                    = new getid3_writetags;
		$tagwriter->filename          = $this->file;
		$tagwriter->tag_encoding      = 'UTF-8';
		$tagwriter->tagformats        = array( 'id3v2.3', 'id3v1' );
		$tagwriter->overwrite_tags    = true;
		$tagwriter->remove_other_tags = true;

		$tagwriter->tag_data = $this->data;

		// write tags
		if ( $tagwriter->WriteTags() ){
			return true;
		} else {
			throw new Exception( implode( ' : ', $tagwriter->errors ) );
		}
	}

	/**
	 *
	 * Initialize the getid3 class
	 *
	 * @param null
	 *
	 * @return object
	 */
	public static function id3(){

		include_once( trailingslashit( RTMEDIA_PATH ) . 'lib/getid3/getid3.php' );

		if ( ! self::$_id3 ){
			self::$_id3 = new getID3;
		}

		return self::$_id3;
	}

	/**
	 *
	 * Sets cover art for mp3 files
	 *
	 * @param array $data
	 *
	 * @param file_type $mime
	 *
	 * @param string $description
	 *
	 * @return null
	 */
	public function set_art( $data, $mime = 'jpeg', $description = 'Description' ){

		if ( $this->data === null ){
			$this->analyze();
		}

		$this->data['attached_picture'] = array();

		$this->data['attached_picture'][0]['data']          = $data;
		$this->data['attached_picture'][0]['picturetypeid'] = 0x03;    // 'Cover (front)'
		$this->data['attached_picture'][0]['description']   = $description;
		$this->data['attached_picture'][0]['mime']          = 'image/' . $mime;
	}

	/**
	 * Analyze data to get media tag info.
	 *
	 * @param string $key
	 *
	 */
	public function __get( $key ){

		if ( ! in_array( $key, $this->tags ) && ! in_array( $key, $this->duration_info ) && ! isset( $this->duration_info[ $key ] ) ){
			throw new Exception( "Unknown property '$key' for class '" . __class__ . "'" );
		}

		if ( $this->data === null ){
			$this->analyze();
		}

		if ( 'image' == $key ){
			return isset( $this->data['attached_picture'] ) ? array(
				'data' => $this->data['attached_picture'][0]['data'],
				'mime' => $this->data['attached_picture'][0]['mime'],
			) : null;
		} else {
			if ( isset( $this->duration_info[ $key ] ) ){
				return $this->duration_info[ $key ];
			} else {
				return isset( $this->data[ $key ] ) ? $this->data[ $key ][0] : null;
			}
		}
	}

	/**
	 * Setting the value.
	 *
	 * @param string $key
	 *
	 * @param string $value
	 *
	 * @return mixed string|error_obj
	 */
	public function __set( $key, $value ){

		if ( ! in_array( $key, $this->tags ) ){
			throw new Exception( "Unknown property '$key' for class '" . __class__ . "'" );
		}
		if ( in_array( $key, $this->readonly_tags ) ){
			throw new Exception( "Tying to set readonly property '$key' for class '" . __class__ . "'" );
		}

		if ( $this->data === null ){
			$this->analyze();
		}

		$this->data[ $key ] = array( $value );
	}

	/**
	 *
	 * Analyze file
	 *
	 * @param null
	 *
	 * @return null
	 *
	 */
	private function analyze(){

		$array_ext  = array( 'ogg', 'm4a', 'mp4', 'webm' );
		$path_parts = pathinfo( $this->file );

		$data = $this->id3->analyze( $this->file );

		$this->duration_info = array( 'duration' => isset( $data['playtime_string'] ) ? ( $data['playtime_string'] ) : '-:--', );

		if ( ! in_array( $path_parts['extension'], $array_ext ) ){
			$this->data = isset( $data['tags'] ) ? array_intersect_key( $data['tags']['id3v2'], array_flip( $this->tags ) ) : array();
		}

		if ( isset( $data['id3v2']['APIC'] ) ){
			$this->data['attached_picture'] = array( $data['id3v2']['APIC'][0] );
		}

		if ( isset( $data['tags']['id3v2']['track_number'] ) ){
			$track = $data['tags']['id3v2']['track_number'][0];
		} else {
			if ( isset( $data['tags']['id3v1']['track'] ) ){
				$track = $data['tags']['id3v1']['track'][0];
			} else {
				$track = null;
			}
		}

		if ( strstr( $track, '/' ) ){
			list( $track, $track_total ) = explode( '/', $track );
			$this->data['track_total'] = array( $track_total );
		}

		$this->data['track'] = array( $track );

	}
}

?>
