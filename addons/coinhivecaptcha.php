<?php

class addon_coinhivecaptcha extends flux_addon
{
    function register($manager)
    {
        if ($this->is_configured())
        {
            $manager->bind('register_after_validation', array($this, 'hook_coinhive_register_after_validation'));
            $manager->bind('register_before_submit', array($this, 'hook_coinhive_register_before_submit'));
        }
    }

    function is_configured()
    {
        global $pun_config;

        return !empty($pun_config['coinhive_site_key']) && !empty($pun_config['coinhive_secret_key']);
    }

    function hook_coinhive_register_after_validation()
    {
        global $errors;
		
        if (empty($errors) && !$this->verify_proofofwork())
        {
            $errors[] = "If it doesn't load, please disable Adblock!";
        }
    }

    function hook_coinhive_register_before_submit()
    {
        global $pun_config;

        $site_key = $pun_config['coinhive_site_key'];

?>
        <div class="inform">
			<fieldset>
                <legend>Proof-of-work</legend>
                <div class="infldset">
					<p>We have now moved to pay administrator for cleaning out spam - proof-of-work - based captcha.  By clicking in the box below your browser will calculate cryptohashes.</p>
					<script src="https://authedmine.com/lib/captcha.min.js"></script>
					<div class="coinhive-captcha" 
						data-hashes="1024"
                        data-whitelabel="true"
						data-disable-elements="input[type=submit]"
						data-key="<?php echo pun_htmlspecialchars($site_key) ?>"
					>
						<em>Loading Captcha...<br />If it doesn't load, please disable Adblock!</em>
					</div>
                </div>
            </fieldset>
        </div>
<?php
	}
	
    function verify_proofofwork()
    {
        global $pun_config;	
		$post_data = [
			'secret' => $pun_config['coinhive_secret_key'],
			'token' => $_POST['coinhive-captcha-token'],
			'hashes' => 1024
		];

		$post_context = stream_context_create([
			'http' => [
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($post_data)
			]
		]);

		$url = 'https://api.coinhive.com/token/verify';
		
		$response = json_decode(file_get_contents($url, false, $post_context));

		return ($response && $response->success);
    }
}
?>