require.config({
	"baseUrl": "wp-content/themes/let-theme/js",
	"paths": {
		"jquery": "vendor/jquery/jquery"
	}
});

require(['jquery'], function($) {
	console.log('Working!!');
});
