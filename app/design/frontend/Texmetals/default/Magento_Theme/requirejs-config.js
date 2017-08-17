var config = {
	map: {
		"*" : {
			"texmetals": "js/jquery.texmetals.default",
			"covervid": "js/lib/covervid/covervid.min",
			"lightslider": "js/lib/lightslider/js/lightslider.min",
			"bxslider": "js/lib/bxslider/js/jquery.bxslider.min",
			"sss": "js/lib/sss/sss",
            "fancybox": "js/lib/fancybox/js/jquery.fancybox"			
        }
	},

	shim: {
			"covervid" : {
				deps: ["jquery"]
			},

			"lightslider" : {
				deps: ["jquery"]
			},

			"bxslider" : {
				deps: ["jquery"]
			},

			"sss" :{
				deps: ["jquery"]
			},
			"fancybox" :{
	            deps: ["jquery"]
	        }
        
		}
	}