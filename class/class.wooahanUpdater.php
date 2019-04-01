<?php
	class wooahanUpdater {
	 
	    private $slug; // plugin slug
	    private $pluginData; // plugin data
	    private $username; // GitHub username
	    private $repo; // GitHub repo name
	    private $pluginFile; // __FILE__ of our plugin
	    private $githubAPIResult; // holds data from GitHub
	    private $accessToken; // GitHub private repo token
	 
	    function __construct( $pluginFile, $gitHubUsername, $gitHubProjectName, $accessToken = '' ) {
	        add_filter( "pre_set_site_transient_update_plugins", array( $this, "setTransitent" ) );
	        add_filter( "plugins_api", array( $this, "setPluginInfo" ), 10, 3 );
	        add_filter( "upgrader_pre_install", array( $this, "preInstall" ), 10, 3 );
	        add_filter( "upgrader_post_install", array( $this, "postInstall" ), 10, 3 );
	 
	        $this->pluginFile       = $pluginFile;
	        $this->username         = $gitHubUsername;
	        $this->repo             = $gitHubProjectName;
	        $this->accessToken      = $accessToken;
	    }
	 
	    // Get information regarding our plugin from WordPress
	    private function initPluginData() {
			$this->slug = plugin_basename( $this->pluginFile );
			$this->pluginData = get_plugin_data( $this->pluginFile );
	    }
	 
	    // Get information regarding our plugin from GitHub
	    private function getRepoReleaseInfo() {
	        if ( ! empty( $this->githubAPIResult ) ){
	            return;
	        }
 
            // Query the GitHub API
            $url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases";

            if ( ! empty( $this->accessToken ) ){
                $url = add_query_arg( array( "access_token" => $this->accessToken ), $url );
            }

            // Get the results
            $this->githubAPIResult = wp_remote_retrieve_body( wp_remote_get( $url ) );

            if ( ! empty( $this->githubAPIResult ) ){
                $this->githubAPIResult = @json_decode( $this->githubAPIResult );
            }

            // Use only the latest release
            if ( is_array( $this->githubAPIResult ) ){
                $this->githubAPIResult = $this->githubAPIResult[0];
            }
	    }
	 
	    // Push in plugin version information to get the update notification
	    public function setTransitent( $transient ) {
	        if ( empty( $transient->checked ) ){
	            return $transient;
	        }
	 
            // Get plugin & GitHub release information
            $this->initPluginData();
            $this->getRepoReleaseInfo();

            $doUpdate = version_compare( $this->githubAPIResult->tag_name, $transient->checked[$this->slug] );

            if ( $doUpdate ){
                    $package = $this->githubAPIResult->zipball_url;

                    if ( ! empty( $this->accessToken ) ){
                        $package = add_query_arg( array( "access_token" => $this->accessToken ), $package );
                    }

                    // Plugin object
                    $obj = new stdClass();
                    $obj->slug = $this->slug;
                    $obj->new_version = $this->githubAPIResult->tag_name;
                    $obj->url = $this->pluginData["PluginURI"];
                    $obj->package = $package;

                    $transient->response[$this->slug] = $obj;
            }
	 
	        return $transient;

	    }
	 
	    // Push in plugin version information to display in the details lightbox
	    public function setPluginInfo( $false, $action, $response ) {

            $this->initPluginData();
            $this->getRepoReleaseInfo();

            if ( empty( $response->slug ) || $response->slug != $this->slug ){
                return false;
            }

            // Add our plugin information
            $response->last_updated = $this->githubAPIResult->published_at;
            $response->slug = $this->slug;
            $response->banners['low'] = 'asdfas.jpg';
            $response->banners['high'] = 'asfasdf.jpg';
            $response->name = "우아한(Wooahan) 우커머스 주문/옵션관리 플러그인";
            $response->plugin_name  = $this->pluginData["Name"];
            $response->version = $this->githubAPIResult->tag_name;
            $response->author = $this->pluginData["AuthorName"];
            $response->homepage = $this->pluginData["PluginURI"];

            // This is our release download zip file
            $downloadLink = $this->githubAPIResult->zipball_url;

            if ( !empty( $this->accessToken ) ){
                $downloadLink = add_query_arg(
                    array( "access_token" => $this->accessToken ),
                    $downloadLink
                );
            }

            $response->download_link = $downloadLink;

            // Load Parsedown
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'Parsedown.php';

            // Create tabs in the lightbox
            $response->sections = array(
                    '설명'   => $this->pluginData["Description"],
                    'changelog'     => class_exists( "Parsedown" )
                            ? Parsedown::instance()->parse( $this->githubAPIResult->body )
                            : $this->githubAPIResult->body
            );

            // Gets the required version of WP if available
            $matches = null;
            preg_match( "/requires:\s([\d\.]+)/i", $this->githubAPIResult->body, $matches );
            if ( ! empty( $matches ) ) {
                if ( is_array( $matches ) ) {
                    if ( count( $matches ) > 1 ) {
                        $response->requires = $matches[1];
                    }
                }
            }

            // Gets the tested version of WP if available
            $matches = null;
            preg_match( "/tested:\s([\d\.]+)/i", $this->githubAPIResult->body, $matches );
            if ( ! empty( $matches ) ) {
                if ( is_array( $matches ) ) {
                    if ( count( $matches ) > 1 ) {
                        $response->tested = $matches[1];
                    }
                }
            }
 
        	return $response;

	    }

	    /**
	     * Perform check before installation starts.
	     *
	     * @param  boolean $true
	     * @param  array   $args
	     * @return null
	     */
	    public function preInstall( $true, $args ){
	        // Get plugin information
	        $this->initPluginData();
	 
	        // Check if the plugin was installed before...
	        $this->pluginActivated = is_plugin_active( $this->slug );
	    }

	    // Perform additional actions to successfully install our plugin
	    public function postInstall( $true, $hook_extra, $result ) {
            global $wp_filesystem;

            // Since we are hosted in GitHub, our plugin folder would have a dirname of
            // reponame-tagname change it to our original one:
            $pluginFolder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname( $this->slug );
            $wp_filesystem->move( $result['destination'], $pluginFolder );
            $result['destination'] = $pluginFolder;

            // Re-activate plugin if needed
            if ( $this->pluginActivated ){
                $activate = activate_plugin( $this->slug );
            }
 
        	return $result;		
	    }
	}

	if( is_admin() ){
		new wooahanUpdater( WOOAHAN__FILE__, 'Bryan-superengine', 'wooahan', 'dc025a18c313707d4a15a0c36653c97e956619d2');
	}
?>