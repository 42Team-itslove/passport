<img src="img/logo.png" id="logo">
<div id="back-home"><a href="http://192.168.102.42/www">返回<span>42首页</span></a></div>
<div id="wrapper">
  <h1>注 册 <span>Register</span></h1>
  <div id="signin-wrap">
    <form id="reg" action="<?= $this->url->get('reg'); ?>" method="POST">
	  <div id="error-msg" class="error-msg"></div>
      <input type="hidden" name="<?php echo $this->security->getTokenKey() ?>">
      <div class="r-item">
        <label for="username">账号</label>
        <input id="username" type="text" name="username" value="" autocomplete="off" placeholder="账号">
        <span class="form-verify-icon"></span>
        <div class="tip">5至60个字符</div>
      </div>
      <div class="r-item">
        <label for="password">密码</label>
        <input id="password" type="password" name="password" value="" autocomplete="off" placeholder="密码">
        <span class="form-verify-icon"></span>
      </div>
      <div class="r-item">
        <label for="nickname">昵称</label>
        <input id="nickname" type="text" name="nickname" value="" autocomplete="off" placeholder="昵称">
        <span class="form-verify-icon"></span>
      </div>
      <div class="f-bt">
        <input type="submit" value="注 册">
      </div>
    </form>
    <div id="advise-regi">
      <em>O R</em>
      <span>已经有账号了？</span>
      <a href="<?= $this->url->get('signin'); ?>"><button>前往登录</button></a>
    </div>
  </div>
  <div id="s-notice">
	  <h2>欢迎来到北溟逐日</h2>
	  <p>这是一个神奇的网站！</p>
	  <p>娱乐 , 导航 , 资源</p>
	  <p>无所不至!</p>
	  <p>不要太沉迷哦～</p>
  </div>
</div>
<script type="text/javascript" src="<?= $this->url->get('js/rsa/jsbn.js'); ?>"></script>
<script type="text/javascript" src="<?= $this->url->get('js/rsa/jsbn2.js'); ?>"></script>
<script type="text/javascript" src="<?= $this->url->get('js/rsa/prng4.js'); ?>"></script>
<script type="text/javascript" src="<?= $this->url->get('js/rsa/rng.js'); ?>"></script>
<script type="text/javascript" src="<?= $this->url->get('js/rsa/rsa.js'); ?>"></script>
<script type="text/javascript" src="<?= $this->url->get('js/rsa/rsa2.js'); ?>"></script>
<script type="text/javascript">
	var publicKey = '';
	var password_value = '';
	$.get('<?= $this->url->get('user/action/getpublickey'); ?>', function(json) {
		publicKey = json.data.pubkey;
	});
	$('#username,#nickname').bind('blur', function() {
		$.get('<?= $this->url->get('user/action/check'); ?>', {
			name: $(this).attr('name'),
			value: $(this).val()
		}, function(json) {
			if (json.statusCode == 200 && json.data.result == false) {
				$('#' + json.data.name).parent().find('.form-verify-icon').removeClass('error').addClass('success');
			} else {
				$('#' + json.data.name).parent().find('.form-verify-icon').removeClass('success').addClass('error');
			}
		});
	});
	$('#reg').bind('form-pre-serialize', function(event,form,options,veto){
		password_value = $('#password').val();
		var rsaKey = new RSAKey();
		rsaKey.setPublic(publicKey, "10001");
		var encryptionResult = rsaKey.encrypt(password_value);
		$('#password').val(encryptionResult);
	});
	$('#reg').ajaxForm({
		beforeSubmit: function(a, f, o) {
			if ($('#username').val().length == 0) {
				$('#username').focus();
				$('#password').val(password_value);
				$('#error-msg').html("请输入注册的帐号");
				$('#error-msg').show();
				return false;
			}
			if ($('#password').val().length == 0) {
				$('#passowrd').focus();
				$('#password').val(password_value);
				$('#error-msg').html("请输入登录密码");
				$('#error-msg').show();
				return false;
			}
			if ($('#nickname').val().length == 0) {
				$('#nickname').focus();
				$('#password').val(password_value);
				$('#error-msg').html("请输入昵称");
				$('#error-msg').show();
				return false;
			}
		},
		success: function(json) {
			if (json.statusCode == 200) {
				var callback = getQueryString('callback');
				if (callback !== null) {
					location.href = callback;
				} else {
					location.href = "<?= $this->url->get('center'); ?>";
				}
				return;
			}

			$('#password').val(password_value);
			 if (json.statusCode == 409) {
				$('#password').focus();
				$('#error-msg').html("注册失败，" + json.explain);
				$('#error-msg').show();
			} else if (json.statusCode == 500) {
				alert('注册失败！ 服务器错误');
			} else {
				alert('注册失败！ 未知原因');
			}
		}
	});
</script>
