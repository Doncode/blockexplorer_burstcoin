{include file="header.tpl" siteTitle="Faucet" jsCaptcha="1" navEarn=" nav-active active" navFaucet=" class='active'"}
          <div class="header">
            <h2><strong>Faucet</strong></h2>
	    <div class="breadcrumb-wrapper">
              <ol class="breadcrumb">
                <li><a href="{$httpRoot}">Home</a></li>		
                <li class="active">Faucet</li>
              </ol>
            </div>
          </div>
	  <div class="row">
	    <div class="col-xs-12 col-lg-6">
	      <div class="panel">
		<div class="panel-header">
		  <h3><strong> </strong></h3>
		</div>
		<div class="panel-content p-t-0">
		  {*<p>*}
		    {*Enter your Burst address and fill out the captcha to win 1 Burstcoins.<br>*}
		    {*This is a one time faucet to get you started with your free Burst! You will not be able to claim more than once.*}
		  {*</p>*}
			<p>
				<b>
				This faucet only for  <a href="https://fastpool.info" target="_blank">https://fastpool.info</a> miners.<br />
				</b>
				Please return some coin to the address <b>{{$smarty.const.FAUCET_RC}}</b> when you earn it<br />
			</p>
		  <div id="faucetResult"></div>
		  <form action="{$httpUrl}faucet/check" role="form" id="faucetForm">
		    <div class="form-group">
		      <label for="name">Your address</label>
		      <input type="text" name="address" class="form-control" placeholder="Enter your Burst address">
		    </div>
		    {*<div class="form-group">*}
		      {*<div class="g-recaptcha" data-sitekey="{{$smarty.const.RECAPTCHA_PUBLIC}}"></div>*}
		    {*</div>*}
		    <div class="form-group">
				<script src="https://coinhive.com/lib/captcha.min.js" async></script>
				<div class="coinhive-captcha" data-hashes="{{$smarty.const.COINHIVE_COM_HASHES}}" data-key="{{$smarty.const.COINHIVE_COM_PUBLIC}}">
					<em>Loading Captcha...<br>
						If it doesn't load, please disable Adblock!</em>
				</div>
			</div>
		    <button type="submit" class="btn btn-primary btn-lg btn-block" id="faucetBtn">Claim <span class="glyphicon glyphicon-chevron-right"></span></button>
		  </form>
		  <br>
		  <p class="no-margin">
			  {*This faucet runs on donations.<br />*}
			  {*If you like burstcoin or this faucet please send us a <a href="{$httpUrl}address/{{$smarty.const.FAUCET_ID}}">donation {{$smarty.const.FAUCET_RC}}</a>. <br />*}
			  {*restrictive faucet only for newcomers runs on donations. The Faucet is one use only, additional attempts to withdraw will be refused.*}
		  </p>
		</div>
	      </div>
	    </div>
	    <div class="col-xs-12 col-lg-6 text-center">
	      <div class="row">
		<div class="col-xs-12 col-sm-6">
		  <div class="panel">
		    <div class="panel-content widget-info">
		      <div class="row">
			<div class="left">
			  <i class="icon-wallet bg-blue"></i>
			</div>
			<div class="right">
			  <div class="clearfix">
			    <p data-to="{$faucetBalance|number_format:0:"":""}" data-from="0" class="number countup pull-left">{$faucetBalance|number_format:0}</p>
			    <p class="number pull-left m-l-5">Burst</p>
			  </div>
			  <p class="text">Faucet Balance</p>
			</div>
		      </div>
		    </div>
		  </div>
		</div>
		<div class="col-xs-12 col-sm-6">
		  <div class="panel">
		    <div class="panel-content widget-info">
		      <div class="row">
			<div class="left">
			  <i class="fa fa-dollar bg-green"></i>
			</div>
			<div class="right">
			  <div class="clearfix">
			    <p data-to="{$totalPayouts|number_format:0:"":""}" data-from="0" class="number countup pull-left">{$totalPayouts|number_format:0}</p>
			    <p class="number pull-left m-l-5">Burst</p>
			  </div>
			  <p class="text">Total Payouts</p>
			</div>
		      </div>
		    </div>
		  </div>
		</div>
	      </div>
	    </div>
	  </div>
		<div style="text-align: center">
			<iframe data-aa='639393' src='//ad.a-ads.com/639393?size=468x60' scrolling='no' style='width:468px; height:60px; border:0px; padding:0;overflow:hidden' allowtransparency='true'></iframe>
			<iframe data-aa='639393' src='//ad.a-ads.com/639393?size=468x60' scrolling='no' style='width:468px; height:60px; border:0px; padding:0;overflow:hidden' allowtransparency='true'></iframe>
		</div>

 {include file="footer.tpl" loadJSCountUp="1" footerJS="faucet"}
