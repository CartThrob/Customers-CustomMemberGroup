<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// must end with _ext
class Cartthrob_customer_member_group_ext
{
    public $settings = array();
    public $name = 'CartThrob Convert Member group on purchase';
    public $version = '1.0.1';
    public $description = 'This extension converts a member to a specific usergroup id upon successful purchase';
	// turns settings on in the backend. 
    public $settings_exist = 'y';
    public $docs_url = 'http://cartthrob.com';
    
    protected $EE;
    
    public function __construct($settings = '')
    {
		$this->EE =& get_instance();
	
		$this->settings = $settings;
    }
    // register hook
    public function activate_extension()
    {
		$this->EE->db->insert(
		    'extensions',
		    array(
			'class' => __CLASS__,
			'method' => 'cartthrob_on_authorize',
			'hook' 	=> 'cartthrob_on_authorize',
			'settings' => '',
			'priority' => 10,
			'version' => $this->version,
			'enabled' => 'y'
		    )
		);
    }
    
    public function update_extension($current='')
    {
		if ($current == '' OR $current == $this->version)
		{
		    return FALSE;
		}
	
		$this->EE->db->update(
		    'extensions',
		    array('version' => $this->version),
		    array('class' => __CLASS__)
		);
    }
    
    public function disable_extension()
    {
		$this->EE->db->delete('extensions', array('class' => __CLASS__));
    }
    
	// simple settings page. Add "group id" input with a default of 7
	function settings()
	{
	    $settings = array();

	    $settings['group_id']      = array('i', '', "7");

	    return $settings;
	}
	// END
    /**
	 * Save Settings
	 *
	 * This function provides a little extra processing and validation
	 * than the generic settings form.
	 *
	 * @return void
	 */
	function save_settings()
	{
		// gotta have post data
	    if (empty($_POST))
	    {
	        show_error($this->EE->lang->line('unauthorized_access'));
	    }
		
	    unset($_POST['submit']);
		// loading the language file
	    $this->EE->lang->loadfile('cartthrob_customer_member_group');

		// get gropu id from post
	    $group_id = $this->EE->input->post('group_id');

		// throwing an error if the group_id is not set right, or if it's a superadmin
	    if ( ! is_numeric($group_id) OR $group_id <= 1)
	    {
			// set the flashdata
	        $this->EE->session->set_flashdata(
	                'message_failure',
	                sprintf($this->EE->lang->line('not_a_valid_group_id'),
	                    $group_id)
	        );
			// redirect
	        $this->EE->functions->redirect(
	            BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=cartthrob_customer_member_group'
	        );
	    }
		
		// update the settings
	    $this->EE->db->where('class', __CLASS__);
	    $this->EE->db->update('extensions', array('settings' => serialize($_POST)));

		// set flashdata and redirect
	    $this->EE->session->set_flashdata(
	        'message_success',
	        $this->EE->lang->line('preferences_updated')
	    );
		$this->EE->functions->redirect(
            BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=cartthrob_customer_member_group'
        );
	}
	
	// this runs when the transaction is authorized. 
    public function cartthrob_on_authorize()
    {
		// getting the group id from the settings
		$group_id = $this->settings['group_id'];  
		// makes sure certain groups aren't converted (super admins, etc)
		if ($this->EE->session->userdata('member_id') && ! in_array($this->EE->session->userdata('group_id'), array(1, $group_id)))
		{
	    	$this->EE->load->model('member_model');
	    	// changing the ID
	    	$this->EE->member_model->update_member($this->EE->session->userdata('member_id'), array('group_id' => $group_id));
		}
    }
    // END
}
//END CLASS

/* End of file ext.cartthrob_purchased_updater.php */
/* Location: ./system/expressionengine/third_party/cartthrob_purchased_updater/ext.cartthrob_purchased_updater.php */