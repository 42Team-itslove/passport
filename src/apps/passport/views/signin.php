<img src="img/logo.png" id="logo">
<div id="back-home"><a href="http://192.168.102.42/www">返回<span>42首页</span></a></div>
<div id="wrapper">
  <h1>登 录 <span>Sign in</span></h1>
  <div id="signin-wrap">
    <form id="signin" action="<?= $this->url->get('signin'); ?>" method="POST">
      <div id="error-msg" class="error-msg"></div>
      <input type="hidden" name="<?php echo $this->security->getTokenKey() ?>">
      <div class="f-item">
         <label for="username">账号</label>
        <input id="username" type="text" name="username" placeholder="账号">
      </div>
      <div class="f-item">
        <label class="password" for="password">密码</label>
        <input id="password" type="password" name="password" placeholder="密码">
      </div>
      <div class="f-att">
        <input id="auto_sigin" type="checkbox" name="auto_signin">
        <label for="auto_sigin">下次自动登录</label>
      </div>
      <div id="forg"><a href="#">找回密码？</a></div>
      <div class="f-bt">
        <input type="submit" value="登 录">
      </div>
    </form>
    <div id="advise-regi">
      <em>O R</em>
      <span>同学，你还没有账号？</span>
      <a href="<?= $this->url->get('reg'); ?>"><button>立即注册</button></a>
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
	$('#signin').bind('form-pre-serialize', function(event,form,options,veto){
		password_value = $('#password').val();
		var rsaKey = new RSAKey();
		rsaKey.setPublic(publicKey, "10001");
		var encryptionResult = rsaKey.encrypt(password_value);
		$('#password').val(encryptionResult);
	});
	$('#signin').ajaxForm({
		beforeSubmit: function(a, f, o) {
			if ($('#username').val().length == 0) {
				$('#username').focus();
				$('#password').val(password_value);
				$('#error-msg').html("请输入登录的帐号");
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
			if (json.statusCode == 403) {
				$('#error-msg').html("登录失败，账号被冻结");
				$('#error-msg').show();
			} else if (json.statusCode == 404) {
				$('#username').focus();
				$('#error-msg').html("登录失败，用户不存在");
				$('#error-msg').show();
			} else if (json.statusCode == 409) {
				$('#password').focus();
				$('#error-msg').html("登录失败，密码错误");
				$('#error-msg').show();
			} else if (json.statusCode == 500) {
				alert('登录失败！ 服务器错误');
			} else {
				alert('登录失败！ 未知原因');
			}
		}
	});
</script>
