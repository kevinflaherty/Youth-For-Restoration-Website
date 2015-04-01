<?php

class LicenseServiceImpl extends AbstractService implements LicenseService
{
	public function get()
	{
		return $this->safeCall('_get');
	}
	
	protected function _get()
	{
		if ( file_exists(LICENSE_PATH) )
			$this->result['license'] = file_get_contents(LICENSE_PATH);
		else
		{
    		$this->result['status'] = 1;
    		$this->result['errorMessage'] = 'License could not be found';
		}
	}
}

?>