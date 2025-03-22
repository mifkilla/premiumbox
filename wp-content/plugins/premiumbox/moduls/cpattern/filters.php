<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	
	add_filter('list_cpattern', 'def_list_cpattern', 100);
	function def_list_cpattern($list){
		asort($list);
		return $list;
	}	
	
	add_action('tab_currency_tab3', 'cpattern_tab_currency_tab3', 50, 2);
	function cpattern_tab_currency_tab3($data, $data_id){
		$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Account number validator','pn'); ?></span></div>
			<?php
			$lists = array();
			$lists[0] = '--'. __('No item','pn') .'--';
			$lists['luna'] = 'Card (LUNA)';
			$lists['crypto_ETH'] = 'ETH (ERC20)';
			$lists['crypto_BNB'] = 'BNB (BEP2)';
			$lists['crypto_IOTX'] = 'IOTX (IOTX)';
			$lists['crypto_STX'] = 'STX (STX)';
			$lists['crypto_AION'] = 'AION (AION)';
			$lists['crypto_BCD'] = 'BCD (Bitcoin Diamond)';
			$lists['crypto_POA'] = 'POA (POA)';
			$lists['crypto_AE'] = 'AE (AE)';
			$lists['crypto_IOST'] = 'IOST (IOST)';
			$lists['crypto_BCH'] = 'BCH (BCH)';
			$lists['bitcoin'] = 'BTC (BTC)';
			$lists['crypto_IOTA'] = 'IOTA (MIOTA)';
			$lists['crypto_BTG'] = 'BTG (Bitcoin Gold)';
			$lists['crypto_BCX'] = 'BCX (BitcoinX)';
			$lists['crypto_ARK'] = 'ARK (Ark)';
			$lists['crypto_TRIG'] = 'TRIG (Triggers)';
			$lists['crypto_BTS'] = 'BTS (BitShares)';
			$lists['crypto_TRX'] = 'TRX (TRC20)';
			$lists['crypto_ONE'] = 'ONE (ONE)';
			$lists['crypto_ONT'] = 'ONT (ONT)';
			$lists['crypto_VITE'] = 'VITE (VITE)';
			$lists['crypto_ALGO'] = 'ALGO (Algorand)';
			$lists['crypto_SC'] = 'SC (Siacoin)';
			$lists['crypto_NEO'] = 'NEO (NEP5)';
			$lists['crypto_PIVX'] = 'PIVX (PIVX)';
			$lists['crypto_ARDR'] = 'ARDR (Ardor)';
			$lists['crypto_CLOAK'] = 'CLOAK (CloakCoin)';
			$lists['crypto_NEBL'] = 'NEBL (Neblio)';
			$lists['crypto_VET'] = 'VET (VeChain)';
			$lists['crypto_EOS'] = 'EOS (EOS)';
			$lists['crypto_ZEC'] = 'ZEC (Zcash)';
			$lists['crypto_ADA'] = 'ADA (Cardano)';
			$lists['crypto_ICX'] = 'ICX (ICON)';
			$lists['crypto_ZEN'] = 'ZEN (Horizen)';
			$lists['crypto_YOYO'] = 'YOYO (YOYOW)';
			$lists['crypto_DOGE'] = 'DOGE (dogecoin)';
			$lists['crypto_HBAR'] = 'HBAR (HBAR)';
			$lists['crypto_RVN'] = 'RVN (Ravencoin)';
			$lists['crypto_NANO'] = 'NANO (NANO)';
			$lists['crypto_WAVES'] = 'WAVES (Waves)';
			$lists['crypto_XRP'] = 'XRP (XRP)';
			$lists['crypto_KAVA'] = 'KAVA (KAVA)';
			$lists['crypto_HCC'] = 'HCC (HealthCare Chain)';
			$lists['crypto_SYS'] = 'SYS (Syscoin)';
			$lists['crypto_COCOS'] = 'COCOS (COCOS)';
			$lists['crypto_STRAT'] = 'STRAT (Stratis)';
			$lists['crypto_THETA'] = 'THETA (Theta Token)';
			$lists['crypto_WAN'] = 'WAN (Wanchain)';
			$lists['crypto_GRS'] = 'GRS (GRS)';
			$lists['crypto_SBTC'] = 'SBTC (SBTC)';
			$lists['crypto_XTZ'] = 'XTZ (Tezos)';
			$lists['crypto_GO'] = 'GO (GoChain)';
			$lists['crypto_HC'] = 'HC (HyperCash)';
			$lists['crypto_ZIL'] = 'ZIL (ZIL)';
			$lists['crypto_SKY'] = 'SKY (Skycoin)';
			$lists['crypto_NAS'] = 'NAS (Nebulas)';
			$lists['crypto_XEM'] = 'XEM (NEM)';
			$lists['crypto_NAV'] = 'NAV (NAV Coin)';
			$lists['crypto_CTXC'] = 'CTXC (CTXC)';
			$lists['crypto_WTC'] = 'WTC (WTC)';
			$lists['crypto_XVG'] = 'XVG (Verge)';
			$lists['crypto_BCHSV'] = 'BCHSV (Bitcoin Cash)';
			$lists['crypto_STEEM'] = 'STEEM (Steem)';
			$lists['crypto_KMD'] = 'KMD (Komodo)';
			$lists['crypto_CMT'] = 'CMT (CyberMiles)';
			$lists['crypto_ATOM'] = 'ATOM (ATOM)';
			$lists['crypto_HIVE'] = 'HIVE (HIVE)';
			$lists['crypto_SOL'] = 'SOL (SOL)';
			$lists['crypto_ETC'] = 'ETC (Ethereum Classic)';
			$lists['crypto_TOMO'] = 'TOMO (TOMO)';
			$lists['crypto_XZC'] = 'XZC (Zcoin)';
			$lists['crypto_GXS'] = 'GXS (GXChain)';
			$lists['crypto_OMNI'] = 'OMNI (OMNI)';
			$lists['crypto_DASH'] = 'DASH (Dash)';
			$lists['crypto_LSK'] = 'LSK (Lisk)';
			$lists['crypto_NULS'] = 'NULS (Nuls)';
			$lists['crypto_BEAM'] = 'BEAM (BEAM)';
			$lists['crypto_DCR'] = 'DCR (Decred)';
			$lists['crypto_LTC'] = 'LTC (LTC)';
			$lists['crypto_NXS'] = 'NXS (Nexus)';
			$lists['crypto_XLM'] = 'XLM (Stellar Lumens)';
			$lists['crypto_QTUM'] = 'QTUM (Qtum)';
			$lists['crypto_VIA'] = 'VIA (Viacoin)';
			$lists['crypto_XMR'] = 'XMR (Monero)';
			$list_cpattern = apply_filters('list_cpattern', array());
			$list_cpattern = (array)$list_cpattern;
			foreach($list_cpattern as $val){
				$lists[is_isset($val,'id')] = is_isset($val,'title');
			}	
				
			$form->select_search('cpattern', $lists, is_isset($data, 'cpattern')); 
			?>	
		</div>
	</div>
	<div class="add_tabs_line">
	</div>	
<?php		
	}

	add_filter('pn_currency_addform_post', 'cpattern_currency_addform_post');
	function cpattern_currency_addform_post($array){
		$array['cpattern'] = is_extension_name(is_param_post('cpattern'));		
		return $array;
	}

}		

function cpattern_check($account, $cpattern){
	
	if($cpattern == 'bitcoin'){
		$cpattern = 'crypto_BTC';
	}
	
	if($cpattern == 'luna'){
		if(!is_valid_credit_card($account)){
			return 0;
		}	
	} elseif(strstr($cpattern,'crypto_')){
		$cpattern = str_replace('crypto_', '', $cpattern);
		$regex = array();
		$regex['ETH'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['BNB'] = '^(bnb1)[0-9a-z]{38}$';
		$regex['IOTX'] = 'io1[qpzry9x8gf2tvdw0s3jn54khce6mua7l]{38}';
		$regex['STX'] = '^([0123456789ABCDEFGHJKMNPQRSTVWXYZ]+)$';
		$regex['AION'] = '^(0x)[0-9A-Fa-f]{64}$';
		$regex['BCD'] = '^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$';
		$regex['POA'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['AE'] = '^ak_[A-Za-z0-9]{47,52}$';
		$regex['IOST'] = '^[A-Za-z0-9_]{5,11}$';
		$regex['BCH'] = '^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$|^[0-9A-Za-z]{42,42}$';
		$regex['BTC'] = '^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$|^(bc1)[0-9A-Za-z]{39,59}$';
		$regex['IOTA'] = '^[A-Z,9]{90}$';
		$regex['BTG'] = '^[AG][a-km-zA-HJ-NP-Z1-9]{25,34}$';
		$regex['BCX'] = '^(X)[0-9A-za-z]{33}$';
		$regex['ARK'] = '^(A)[A-Za-z0-9]{33}$';
		$regex['TRIG'] = '^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$';
		$regex['BTS'] = '^[a-z]{1}[a-z0-9-\.]{2,62}$';
		$regex['TRX'] = '^T[1-9A-HJ-NP-Za-km-z]{33}$';
		$regex['ONE'] = '^(one1)[a-z0-9]{38}$';
		$regex['ONT'] = '^(A)[A-Za-z0-9]{33}$';
		$regex['VITE'] = '^(vite_)[a-z0-9]{50}$';
		$regex['ALGO'] = '^[A-Z0-9]{58,58}$';
		$regex['SC'] = '^[A-Za-z0-9]{76}$';
		$regex['NEO'] = '^(A)[A-Za-z0-9]{33}$';
		$regex['PIVX'] = '^(D)[0-9A-za-z]{33}$';
		$regex['ARDR'] = '^(ARDOR-)[0-9A-Za-z]{4}(-)[0-9A-Za-z]{4}(-)[0-9A-Za-z]{4}(-)[0-9A-Za-z]{5}$';
		$regex['CLOAK'] = '^[C|B][A-Za-z0-9]{33}$';
		$regex['NEBL'] = '^N[A-Za-z0-9]{33}$';
		$regex['VET'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['EOS'] = '^[1-5a-z\.]{1,12}$';
		$regex['ZEC'] = '^(t)[A-Za-z0-9]{34}$';
		$regex['ADA'] = '^([1-9A-HJ-NP-Za-km-z]{59})|([1-9A-HJ-NP-Za-km-z]{104})$';
		$regex['ICX'] = '^(hx)[A-Za-z0-9]{40}$';
		$regex['ZEN'] = '^(z)[0-9A-za-z]{34}$';
		$regex['YOYO'] = '^[0-9]{5,20}$';
		$regex['DOGE'] = '^(D|A|9)[a-km-zA-HJ-NP-Z1-9]{33,34}$';
		$regex['HBAR'] = '^0.0.\d{1,6}$';
		$regex['RVN'] = '^[Rr]{1}[A-Za-z0-9]{33,34}$';
		$regex['NANO'] = '^(xrb_|nano_)[13456789abcdefghijkmnopqrstuwxyz]{60}';
		$regex['WAVES'] = '^(3P)[0-9A-Za-z]{33}$';
		$regex['XRP'] = '^r[1-9A-HJ-NP-Za-km-z]{25,34}$';
		$regex['KAVA'] = '^(kava1)[0-9a-z]{38}$';
		$regex['HCC'] = '^(H)[A-Za-z0-9]{33}$';
		$regex['SYS'] = '(S)[A-Za-z0-9]{32,33}|sys1[qpzry9x8gf2tvdw0s3jn54khce6mua7l]{39}';
		$regex['COCOS'] = '^[a-z]{1}[a-z0-9-]{3,61}[a-z0-9]{1}$';
		$regex['STRAT'] = '^(S)[A-Za-z0-9]{33}$';
		$regex['THETA'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['WAN'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['GRS'] = '(F|3)[0-9A-za-z]{33}|grs1[qpzry9x8gf2tvdw0s3jn54khce6mua7l]{39}';
		$regex['SBTC'] = '^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$';
		$regex['XTZ'] = '^(tz[1,2,3]|KT1)[a-zA-Z0-9]{33}$';
		$regex['GO'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['HC'] = '^[H][a-km-zA-HJ-NP-Z1-9]{26,35}$';
		$regex['ZIL'] = 'zil1[qpzry9x8gf2tvdw0s3jn54khce6mua7l]{38}';
		$regex['SKY'] = '^[0-9A-Za-z]{26,35}$';
		$regex['NAS'] = '^n1[a-zA-Z0-9]{33}$';
		$regex['XEM'] = '^(NA|NB|NC|ND)[a-zA-z0-9]{38}$';
		$regex['NAV'] = '^(N)[0-9A-za-z]{33}$';
		$regex['CTXC'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['WTC'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['XVG'] = '^(D)[A-Za-z0-9]{33}$';
		$regex['BCHSV'] = '^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$|^[0-9A-Za-z]{42,42}$';
		$regex['STEEM'] = '^[a-z][a-z0-9-.]{0,14}[a-z0-9]$';
		$regex['KMD'] = '^(R)[A-Za-z0-9]{33}$';
		$regex['CMT'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['ATOM'] = '^(cosmos1)[0-9a-z]{38}$';
		$regex['HIVE'] = '^[a-z][a-z0-9-.]{0,14}[a-z0-9]$';
		$regex['SOL'] = '^[0-9a-zA-Z]{32,44}$';
		$regex['ETC'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['TOMO'] = '^(0x)[0-9A-Fa-f]{40}$';
		$regex['XZC'] = '^[a|Z|3|4][0-9A-za-z]{33}$';
		$regex['GXS'] = '^[a-z]{1}[a-z0-9-.]{2,62}$';
		$regex['OMNI'] = '^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$|^(bc1)[0-9A-Za-z]{39}$';
		$regex['DASH'] = '^[X|7][0-9A-Za-z]{33}$';
		$regex['LSK'] = '^[0-9]{12,22}[L]$';
		$regex['NULS'] = '^NULS[a-km-zA-HJ-NP-Z1-9]{33,33}$';
		$regex['BEAM'] = '^[A-Za-z0-9]{66,67}$|^(beam:)[A-Za-z0-9]{66,67}$';
		$regex['DCR'] = '^(Ds|Dc)[0-9A-Za-z]{33}$';
		$regex['LTC'] = '^(L|M|3)[A-Za-z0-9]{33}$|^(ltc1)[0-9A-Za-z]{39}$';
		$regex['NXS'] = '^(2|8)[A-Za-z0-9]{50}$';
		$regex['XLM'] = '^G[A-D]{1}[A-Z2-7]{54}$';
		$regex['QTUM'] = '^[Q|M][A-Za-z0-9]{33}$';
		$regex['VIA'] = '((V|E)[A-Za-z0-9]{33})|(via1[qpzry9x8gf2tvdw0s3jn54khce6mua7l]{39})';
		$regex['XMR'] = '^[48][a-zA-Z|\d]{94}([a-zA-Z|\d]{11})?$';
		$reg = trim(is_isset($regex, $cpattern));
		if($reg){
			if(!preg_match("/{$reg}/", $account, $matches)){
				return 0;	
			}
		}
	}
	
	return apply_filters('cpattern_check', 1, $account, $cpattern);
}

add_filter('error_bids', 'cpattern_error_bids', 0, 6);
function cpattern_error_bids($error_bids, $account1, $account2, $direction, $vd1, $vd2){
	
	if($vd1->cpattern and $account1){
		if(!cpattern_check($account1, $vd1->cpattern)){
			$error_bids['error_fields']['account1'] = __('error in account number','pn');
		}
	}
			
	if($vd2->cpattern and $account2){
		if(!cpattern_check($account2, $vd2->cpattern)){
			$error_bids['error_fields']['account2'] = __('error in account number','pn');
		}
	}	
	
	return $error_bids;
}				