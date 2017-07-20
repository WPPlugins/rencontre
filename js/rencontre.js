/* Rencontre */
var emot=["",":-)",":-(",":-d",":-D",";;)","8-)",":-/",":-3",":-r",":-p",":-*",":-K",":-O",":-S","B-)"],veille,vue,scrute,moi='',toi='',img='',data,image=[],rs=1,ps='',rmap=0,gc=0,gm,rencWidg=1;
if(typeof noEmot==='undefined')noEmot=0;
if(typeof f_tete_zoom!='function')function f_tete_zoom(){}
if(typeof f_tete_normal!='function')function f_tete_normal(){}
if(typeof rencUrl==='undefined'){
	rencUrl=window.location.protocol+'//'+window.location.hostname;
	rencWidg=0;
}
/* fonctions classiques */
function f_min(f,x,y,z){
	var c=0,d=document.forms[x][y],e=document.forms[x][z];
	f=parseInt(f);
	for(v=0;v<e.length;v++){
		if(parseInt(d.options[v].value)==f)c=v;
		if(parseInt(e.options[v].value)<=f)e.options[v].disabled=true;
		else e.options[v].disabled=false;
	}
	if(e.options[e.selectedIndex]&&f>parseInt(e.options[e.selectedIndex].value))e.selectedIndex=c;
}
function f_max(f,x,y,z){
	var c=0,d=document.forms[x][z],e=document.forms[x][y];
	f=parseInt(f);
	for(v=0;v<e.length;v++){
		if(parseInt(d.options[v].value)==f)c=v;
		if(parseInt(e.options[v].value)>=f)e.options[v].disabled=true;
		else e.options[v].disabled=false;
	}
	if(e.options[e.selectedIndex]&&f<parseInt(e.options[e.selectedIndex].value))e.selectedIndex=c;
}
function f_onglet(f){
	document.getElementById('portraitTable'+last).style.display='none';
	document.getElementById('portraitTable'+f).style.display='table';
	document.getElementById('portraitOnglet'+last).className='portraitOnglet';
	document.getElementById('portraitOnglet'+f).className='portraitOnglet rencTab';
	last=f;
}
last=0;
function f_vignette(f,img){
	ff=rencUrl+"/wp-content/uploads/portrait/"+Math.floor((f)/10000)+"/"+img+".jpg?r="+Math.random();
	document.getElementById('portraitGrande').src=ff;
}
function f_vignette_change(f,img){
	f_vignette(f,img);
	document.getElementById('changePhoto').innerHTML='';
}
function f_supp_photo(f){
	document.getElementById('changePhoto').innerHTML=rencobjet.supp_photo+'&nbsp;<a href="javascript:void(0)" class="rencSupp" onClick="document.forms[\'portraitPhoto\'].elements[\'a1\'].value=\'suppImg\';document.forms[\'portraitPhoto\'].elements[\'a2\'].value=\''+f+'\';document.forms[\'portraitPhoto\'].elements[\'renc\'].value=\'edit\';document.forms[\'portraitPhoto\'].submit();" title="'+rencobjet.supp_la_photo+'">';
}
function f_photoPop_display(f){
	if(f.files&&f.files[0]){
		var r=new FileReader(),b=document.getElementById('popPhoto');
		r.onload=function(e){
			while(b.firstChild)b.removeChild(b.firstChild);
			var a=document.createElement('img');
			a.src=e.target.result;
			a.className='pop-photo';
			a.style.width='auto';
			a.style.height='60px';
			b.appendChild(a);
			a=document.createElement('span');
			a.className='rotateLeft';
			a.onclick=function(){
				var r=jQuery('#popPhoto img').getRotateAngle(),s=Number(r)-90;
				jQuery('#popPhoto img').rotate({angle:r,animateTo:s});
			};
			b.appendChild(a);
			a=document.createElement('span');
			a.className='rotateRight';
			a.onclick=function(){
				var r=jQuery('#popPhoto img').getRotateAngle(),s=Number(r)+90;
				jQuery('#popPhoto img').rotate({angle:r,animateTo:s});
			};
			b.appendChild(a);
		}
		r.readAsDataURL(f.files[0]);
	}
}
function f_plus_photoPop_submit(f){
	var a=document.forms['portraitPhotoPop'];
	a.elements['a1'].value='plusImg';
	a.elements['a2'].value=f;
	a.elements['rotate'].value=jQuery('#popPhoto img').getRotateAngle();
	a.elements['renc'].value='edit';
	a.submit();
}
function f_suppAll_photo(){
	var a=document.forms['portraitPhoto'];
	a.elements['a1'].value='suppImgAll';
	a.elements['renc'].value='edit';
	a.submit();
}
function f_sauv_profil(f){
	var a=document.forms['portraitChange'];
	a.elements['a1'].value='sauvProfil';
	a.elements['a2'].value=f;
	a.elements['renc'].value='edit';
	a.submit();
}
function f_fantome(){
	document.getElementById('rencFantome').style.display='none';
	document.cookie="rencfantome=oui";
}
function f_mod_nouveau(f){
	var a=0,c=document.forms['formNouveau'],b=c.elements,d,e;
	if(b['sex']&&b['sex'].value=='')a++;
	if(b['jour']&&b['jour'].value=='')a++;
	if(b['mois']&&b['mois'].value=='')a++;
	if(b['annee']&&b['annee'].value=='')a++;
	if(b['pays']&&b['pays'].value=='')a++;
	if(b['taille']&&b['taille'].value=='')a++;
	if(b['poids']&&b['poids'].value=='')a++;
	if(b['zsex']&&b['zsex'].value=='')a++;
	if(b['zsex[]']){
		d=0;
		if(!b['zsex[]'].length){
			if(b['zsex[]'].checked==true)d++;
		}
		else for(v=0;v<b['zsex[]'].length;++v){
			if(b['zsex[]'][v].checked)d++;
		};
		if(d==0)a++;
	}
	if(b['zageMin']&&b['zageMin'].value=='')a++;
	if(b['zageMax']&&b['zageMax'].value=='')a++;
	if(b['zrelation']&&b['zrelation'].value=='')a++;
	if(b['zrelation[]']){
			d=0;
			if(!b['zrelation[]'].length){
				if(b['zrelation[]'].checked==true)d++;
			}
			else for(v=0;v<b['zrelation[]'].length;++v){
				if(b['zrelation[]'][v].checked)d++;
			};
			if(d==0)a++;
		}
	if(b['ville']&&b['ville'].value=='')a++;
	if(a==0){
		b['a1'].value=f;
		e=document.getElementById('fastregInfo');
		if(e!==null)e.style.display='none';
		c.submit();
	}
	else document.getElementById('rencAlert').innerHTML=a+'&nbsp;'+rencobjet.champs_incomplets;
}
function f_fin(f){
	if(confirm(rencobjet.conf_supp_compte)){
	var a=document.forms['formFin'];
		a.elements['renc'].value='fin';
		a.submit();
	}
}
function f_trouve(f){
	var a=document.forms['formTrouve'];
	if(f===0)f_addCountSearch();
	a.elements['renc'].value='liste';
	a.submit();
}
function f_quickTrouve(f){
	var a=document.forms['formMonAccueil'];
	if(f===0)f_addCountSearch();
	a.elements['renc'].value='qsearch';
	a.submit();
}
/* Popup avec photo FB */
function f_plus_photoFB_submit(f,g){
	var a=document.forms['portraitPhotoPop'];
	a.elements['a1'].value='plusImg';
	a.elements['a2'].value=f+'|'+g;
	a.elements['renc'].value='edit';
	a.submit();
}
function f_FBLogin(f){
	FB.getLoginStatus(function(response){
		if(response.status==='connected')f_FBProfileImage(f);
		else if(response.status==='not_authorized'){
			FB.login(function(response){
				if(response&&response.status==='connected')f_FBProfileImage(f);
			});
		}
		else{
			FB.login(function(response){
				if(response&&response.status==='connected')f_FBProfileImage(f);
			});
		}
	});
}
function f_FBProfileImage(f){
	FB.api("/me/picture?type=large",function(response){
		var p=response.data.url.split('https://')[1],a=document.createElement("img");
		a.src='http://'+p;
		a.className='pop-photo';
		a.style.width='auto';
		a.style.height='60px';
		a.onclick=function(){f_plus_photoFB_submit(f,'http://'+p);};
		document.getElementById('popPhoto').appendChild(a);
	});  
}
function f_msgEmot(d){
	for(v=1;v<16;v++){
		d1=document.createElement("img");
		d1.src=rencUrl+"/wp-content/plugins/rencontre/images/"+v+".gif";
		d1.alt=v;
		d1.onclick=function(){
			a=formEcrire.elements['contenu'];
			a.value+=emot[this.alt];
		};
		d.appendChild(d1);
	};
}
function f_msgEmotContent(f){
	for(v in f){
		f[v].innerHTML=f_emot(f[v].innerHTML);
	}
}
/* Menu via WP */
function f_renc_menu(f,i,c){
	if(c){
		jQuery("."+c).parent().children().removeClass("current-menu-item");
		jQuery("."+c).addClass("current-menu-item");
		if(jQuery("#"+c).length!=0)jQuery("#"+c+" li").addClass("current");
	}
	jQuery(".rencMenuCard").click(function(){
		document.forms['rencMenu'].elements['renc'].value='card';
		document.forms['rencMenu'].submit();
	});
	if(f.edit)jQuery(".rencMenuEdit").click(function(){
		document.forms['rencMenu'].elements['renc'].value='edit';
		document.forms['rencMenu'].submit();
	});
	else jQuery(".rencMenuEdit").addClass("menu-item-off");
	if(f.msg)jQuery(".rencMenuMsg").click(function(){
		document.forms['rencMenu'].elements['renc'].value='msg';
		document.forms['rencMenu'].submit();
	});
	else jQuery(".rencMenuMsg").addClass("menu-item-off");
	if(f.search)jQuery(".rencMenuSearch").click(function(){
		document.forms['rencMenu'].elements['renc'].value='gsearch';
		document.forms['rencMenu'].submit();
	});
	else jQuery(".rencMenuSearch").addClass("menu-item-off");
	jQuery(".rencMenuAccount").click(function(){
		document.forms['rencMenu'].elements['renc'].value='account';
		document.forms['rencMenu'].submit();
	});
	jQuery(".rencMenuC1").click(function(){
		document.forms['rencMenu'].elements['renc'].value='c1';
		document.forms['rencMenu'].submit();
	});
	jQuery(".rencMenuC2").click(function(){
		document.forms['rencMenu'].elements['renc'].value='c2';
		document.forms['rencMenu'].submit();
	});
}
/* fonctions avec appel Ajax */
function f_region_select(f,g,x){
	jQuery(document).ready(function(){
		jQuery('#'+x).empty();
		jQuery.post(g,{'action':'regionBDD','pays':f},function(r){
			jQuery('#'+x).append(r);
		});
	});
}
function f_ajax_sourire(f,g){
	jQuery(document).ready(function(){
		jQuery.post(g,{'action':'sourire','to':f},function(r){
			jQuery('#infoChange').append(r);
			window.setTimeout('document.getElementById("infoChange").innerHTML=""',3000);
		});
	});
}
function f_voir_msg(f,g,h,ho){
	jQuery(document).ready(function(){
		jQuery.post(g,{'action':'voirMsg','msg':f,'alias':h,'ho':ho},function(r){
			jQuery('#rencMsg').empty();
			jQuery('#rencMsg').append(r.substring(0,r.length-1));
		});
	});
}
function f_nouveau(f,g,e){
	var a=0,d=0,c=document.forms['formNouveau'],b=c.elements,v;
	jQuery('#rencAlert').empty();
	if(e==0){
		if(b['sex']&&b['sex'].value=='')a++;
		if(b['jour']&&b['jour'].value=='')a++;
		if(b['mois']&&b['mois'].value=='')a++;
		if(b['annee']&&b['annee'].value=='')a++;
		if(b['taille']&&b['taille'].value=='')a++;
		if(b['poids']&&b['poids'].value=='')a++;
	}
	else if(e==1){
		if(b['pays']&&b['pays'].value=='')a++;
		if(b['ville']&&b['ville'].value=='')a++;
	}
	else if(e==2){
		if(b['zsex']&&b['zsex'].value=='')a++;
		if(b['zsex[]']){
			d=0;
			if(!b['zsex[]'].length){
				if(b['zsex[]'].checked==true)d++;
			}
			else for(v=0;v<b['zsex[]'].length;++v){
				if(b['zsex[]'][v].checked)d++;
			};
			if(d==0)a++;
		}
		if(b['zageMin']&&b['zageMin'].value=='')a++;
		if(b['zageMax']&&b['zageMax'].value=='')a++;
		if(b['zrelation']&&b['zrelation'].value=='')a++;
		if(b['zrelation[]']){
			d=0;
			if(!b['zrelation[]'].length){
				if(b['zrelation[]'].checked==true)d++;
			}
			else for(v=0;v<b['zrelation[]'].length;++v){
				if(b['zrelation[]'][v].checked)d++;
			};
			if(d==0)a++;
		}
	}
	else if(e==3){
		if(b['pseudo']&&b['pseudo'].value.length<1)a++;
		if(b['pass1']&&b['pass1'].value.length<6)a++;
		if(b['pass2']&&b['pass2'].value.length<6)a++;
	}
	if(e!=3&&a==0){
		b['a1'].value=f;
		c.submit();
	}
	else if(e!=3&&a!=0)document.getElementById('rencAlert').innerHTML=a+' '+rencobjet.champs_incomplets;
	else if(e==3)jQuery.post(g,{'action':'testPseudo','name':b['pseudo'].value},function(r){
		scroll(0,0);
		if(r==0){
			document.getElementById('rencAlert').innerHTML=rencobjet.mauvais_pseudo;
			a=99;
			return;
		}
		if(a==0){
			if(b['pass1'].value!=b['pass2'].value)document.getElementById('rencAlert').innerHTML=rencobjet.nouv_pass_diff;
			else{
				document.getElementById('buttonPass').style.visibility="hidden";
				jQuery.post(g,{'action':'iniUser','pseudo':b['pseudo'].value,'pass1':b['pass1'].value},function(r){
					c.submit();
				});
			}
		}
		else if(a!=99)document.getElementById('rencAlert').innerHTML=a+' '+rencobjet.champs_incomplets;
	});
}
function f_password(f0,f1,f2,f,g){
	if(f1.length<6)return;
	jQuery('#rencAlert1').empty();
	if(f1!=f2){
		document.getElementById('rencAlert1').innerHTML=rencobjet.nouv_pass_diff;
		window.setTimeout('document.getElementById("rencAlert1").innerHTML=""',3000);
	}
	else{
		document.getElementById('buttonPass').style.visibility="hidden";
		jQuery.post(g,{'action':'testPass','id':f,'pass':f0,'nouv':f1},function(r){
			if(r!=0){
				d=document.forms['formPass'];
				d.elements['renc'].value='paswd';
				d.elements['id'].value=f;
				d.submit();
			}
			else{
				document.getElementById('rencAlert1').innerHTML=rencobjet.pass_init_faux +r.substring(0,r.length-1);
				window.setTimeout('document.getElementById("rencAlert1").innerHTML=""',3000);
				document.getElementById('buttonPass').style.visibility="visible";
			}
		});
	}
}
function f_city(f,g,h,i){
	if(document.getElementById('rencTMap')!==null)document.getElementById('rencTMap').style.display='none';
	jQuery(document).ready(function(){
		if(f.length>2){
			jQuery.post(g,{'action':'city','city':f,'iso':h,'ch':i},function(r){
				jQuery('#rencCity').empty();
				jQuery('#rencCity').append(r.substring(0,r.length-1));
				if(i==1&&r==0&&document.getElementById('rencMap')===null&&rmap==0)f_cityMap(f,document.getElementById('rencPays').options[document.getElementById('rencPays').selectedIndex].text,'0',1);
			});
		}
	});
}
function f_cityMap(f,lat,lon,h){
	document.getElementById('rencVille').value=f;
	document.getElementById('rencCity').innerHTML='';
	if(document.getElementById('rencMap')!==null)document.getElementById('rencMap').style.display='block';
	if(document.getElementById('rencTMap')!==null)document.getElementById('rencTMap').style.display='block';
	jQuery(document).ready(function(){
		if(typeof google!='undefined'){
			gc=0;
			if(lon=="0"){
				j=new google.maps.Geocoder();
				j.geocode({'address':lat},function(r,s){
					if(s==google.maps.GeocoderStatus.OK){
						rmap=new google.maps.Map(document.getElementById('rencMap'),{
							center:r[0].geometry.location,
							zoom:5,
							mapTypeId:google.maps.MapTypeId.ROADMAP,
							disableDefaultUI:true,
							zoomControl:true
						});
						f_cityMark(lat,lon);
					}
				});
			}
			else{
				rmap=new google.maps.Map(document.getElementById('rencMap'),{
					center:{
						lat:parseFloat(lat),
						lng:parseFloat(lon)
					},
					zoom:8,
					mapTypeId:google.maps.MapTypeId.ROADMAP,
					disableDefaultUI:true,
					zoomControl:true
				});
				f_cityMark(lat,lon);
			}
		}
	});
}
function f_cityMark(lat,lon){
	if(lon=="0"){
		lon=rmap.getCenter();
		lat=lon.lat();
		lon=lon.lng();
	}
	gm=new google.maps.Marker({
		position:{
			lat:parseFloat(lat),
			lng:parseFloat(lon)
		},
		map:rmap
	});
	google.maps.event.addListener(rmap,'click',function(e){
		gm.setPosition({
			lat:e.latLng.lat(),
			lng:e.latLng.lng()
		});
		document.getElementById('rencTMap').style.display='block';
		if(gc)gc.setCenter(gm.getPosition());
	});
}
function f_cityOk(){
	if(typeof google!='undefined'){
		p=gm.getPosition();
		rmap.setCenter(p);
		document.getElementById('gps').value=p.lat()+'|'+p.lng();
	}
	document.getElementById('rencTMap').style.display='none';
}
function f_cityKm(f){
	f=parseInt(f.replace(/[^0-9]/g,''));
	if(f==0)return;
	if(!gc){
		gc=new google.maps.Circle({
			strokeColor:'#00a',
			strokeOpacity:.5,
			strokeWeight:2,
			fillColor:'#00a',
			fillOpacity:.2,
			map:rmap,
			center:gm.getPosition(),
			radius:(parseInt(f)*1000)
		});
		google.maps.event.addListener(gc,'click',function(e){
			gm.setPosition({
				lat:e.latLng.lat(),
				lng:e.latLng.lng()
			});
			document.getElementById('rencTMap').style.display='block';
			gc.setCenter(gm.getPosition());
		});
	}
	else{
		gc.setCenter(gm.getPosition());
		gc.setRadius(parseInt(f)*1000);
	}
}
function f_mapCherche(f,lat,lon,s){
	jQuery(window).load(function(){
		rmap=new google.maps.Map(document.getElementById('rencMap2'),{
			center:{
				lat:parseFloat(lat),
				lng:parseFloat(lon)
			},
			zoom:7,
			mapTypeId:google.maps.MapTypeId.ROADMAP,
			disableDefaultUI:true,
			zoomControl:true
		});
		gm=new google.maps.Marker({
			position:{
				lat:parseFloat(lat),
				lng:parseFloat(lon)
			},
			map:rmap
		});
		jQuery.each(f,function(k,v){
			if(v[2]!="0")im=s+'/wp-content/uploads/portrait/'+Math.floor(v[2]/10000)+'/'+v[5]+'.jpg';
			else im=s+"/wp-content/plugins/rencontre/images/yellow-dot.png";
			gn=new google.maps.Marker({
				position:{
					lat:parseFloat(v[0]),
					lng:parseFloat(v[1])
				},
				map:rmap,
				icon:im,
				title:v[3]
			});
			google.maps.event.addListener(gn,'click', function(e){
				var a=document.forms['rencMenu'];
				a.elements['renc'].value='card';
				a.elements['id'].value=v[4];
				a.submit();
			});
		});
	});
}
function f_fastregMail(g){
	jQuery.post(g,{'action':'fastregMail'},function(r){
		jQuery('#rencAlert1').empty();
		jQuery('#rencAlert1').append(r.substring(0,r.length-1));
		document.cookie="rencfastregMail=oui";
		window.setTimeout('document.getElementById("rencAlert1").innerHTML=""',3000);
	});
}
function f_addCountSearch(){
	jQuery.post(rencobjet.wpajax,{'action':'addCountSearch'});
}
/* Tchat */
function f_bip(){
	at={
		"mp3":"audio/mpeg",
		"mp4":"audio/mp4",
		"ogg":"audio/ogg",
		"wav":"audio/wav"
	};
	am=["bip.ogg","bip.mp3"];
	bip=document.createElement('audio');
	if(bip.canPlayType){
		for(i=0;i<am.length;i++){
			sl=document.createElement('source');
			sl.setAttribute('src',rencUrl+'/wp-content/plugins/rencontre/js/'+am[i]);
			if (am[i].match(/\.(\w+)$/i))sl.setAttribute('type',at[RegExp.$1]);
			bip.appendChild(sl);
		}
		bip.load();
		bip.playclip=function(){
			bip.pause();
			bip.currentTime=0;bip.play();
		};
		return bip;
	}
	else return;
};
var cd=f_bip();
function f_emot(f){
	if(noEmot==1)return f;
	if(typeof f!=='undefined')for(var v=1;v<16;v++){
		var r=emot[v].replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
		var re=new RegExp(r,'g');
		f=f.replace(re,"<img src='"+rencUrl+"/wp-content/plugins/rencontre/images/"+v+".gif' alt='' />");
	};
	return f;
}
function f_tchat_veille(s,p){
	if(!s)s='';
	if(p)ps=p;
	jQuery(document).ready(function(){
		jQuery.post(rencobjet.ajaxchat,{'tchat':'tchatVeille','fm':rencobjet.mid},function(r){
			if(r){
				clearInterval(veille);
				if(r==s)f_tchat_ok(rencobjet.mid,r,rencobjet.ajaxchat);
				else if(r!=rencobjet.mid)f_tchat_dem(rencobjet.mid,r);
			}
		});
	});
}
function f_tchat(f,t,g,p,s){
	ps=s;
	var a=document.getElementById('rencTchat'),b=document.createElement("div");
	a.innerHTML="";
	b.className="top";
	b.innerHTML="Tchat"+(ps?" <em>("+ps+")</em>":"");
	a.appendChild(b);
	var b0=document.createElement("div");
	b0.id="bcam";
	if(document.getElementById('rencCam')!==null){
		b0.className="cam";
		b0.title="webcam";
		b0.onclick=function(){
			if(moi!=''){
				moi='';f_camOff();
			}
			else webcam(f,t);
		};
	}
	var b1=document.createElement("span");
	b1.innerHTML="X";
	b1.onclick=function(){
		f_tchat_fin(f,t,g);
	};
	b.appendChild(b1);
	b.appendChild(b0);
	var c=document.createElement("div");
	c.id="contenu";
	a.appendChild(c);
	if(noEmot!=1){
		var d=document.createElement("div");
		d.className="emot";
		a.appendChild(d);
	}
	var i=document.createElement("input");
	i.value=rencobjet.ecrire_appuyer;
	i.id="inTchat";
	i.disabled=true;
	i.onfocus=function(){
		if(this.className!="actif"){
			this.className="actif";
			this.style.color="#222";
			this.value="";
		}
	};
	i.onkeypress=function(e){
		if (e.keyCode==13&&this.value){
			f_tchat_envoi(f,t,this.value,g);
			this.value="";
		}
	};
	a.appendChild(i);
	if(noEmot!=1)for(v=1;v<16;v++){
		var d1=document.createElement("img");
		d1.src=rencUrl+"/wp-content/plugins/rencontre/images/"+v+".gif";
		d1.alt=v;
		d1.onclick=function(){
			a=document.getElementById('inTchat');
			if(a.className!="actif"){
				a.className="actif";
				a.style.color="#222";
				a.value="";
			}
			if(!a.disabled){
				a.value+=emot[this.alt];
				a.focus();
			}
		};
		d.appendChild(d1);
	};
	a.style.visibility="visible";
	clearInterval(veille);
	if(p==1){
		var c2=document.createElement("div");
		c2.className="az";
		c2.innerHTML=rencobjet.tchat_attendre;
		c.appendChild(c2);
		f_tchat_debut(f,t,g);
	}
	else if(p==0){
		scrute=setInterval(function(){
			f_tchat_scrute(f,t,g);
		},2023);
	};
}
function f_tchat_debut(f,t,g){
	jQuery(document).ready(function(){
		jQuery.post(g,{'tchat':'tchatDebut','fm':f,'to':t},function(r){
			scrute=setInterval(function(){
				f_tchat_scrute(f,t,g);
			},2023);
		});
	});
}
function f_tchat_scrute(f,t,g){
	jQuery(document).ready(function(){
		if(document.getElementById('inTchat')){
			jQuery.post(g,{'tchat':'tchatScrute','fm':f,'to':t},function(r){
				if(r=='::'+f+'::')f_tchat_off();
				else if(r){
					if(document.getElementById('inTchat').disabled==true)f_tchat_on();
					f_tchat_actualise("",r,f,t);
				}
			});
		}
	});
}
function f_tchat_dem(f,t){
	var a=document.getElementById('rencTchat'),b=document.createElement("div");
	a.innerHTML="";
	b.className="top";
	b.innerHTML="Tchat";
	a.appendChild(b);
	var b1=document.createElement("span");
	b1.innerHTML="X";
	b1.onclick=function(){
		f_tchat_fin(f,t,rencobjet.ajaxchat);
	};
	b.appendChild(b1);
	var c=document.createElement("div");
	c.id="contenu";
	c.innerHTML=rencobjet.demande_tchat+'&nbsp;:&nbsp;';
	a.appendChild(c);
	jQuery(document).ready(function(){
		jQuery.post(rencobjet.wpajax,{'action':'miniPortrait2','id':t},function(r){
			r=r.split('|');
			ps=r[0];
			c.innerHTML+=r[1].substring(0,r[1].length-1);
			var c1=document.createElement("div");
			c1.className="button right";
			c1.innerHTML=rencobjet.ignorer;
			c1.onclick=function(){
				f_tchat_fin(f,t,rencobjet.ajaxchat);
			};
			c.appendChild(c1);
			var c2=document.createElement("div");
			c2.className="button right";
			c2.innerHTML=rencobjet.accepter;
			c2.onclick=function(){
				f_tchat_ok(f,t,rencobjet.ajaxchat);
			};
			c.appendChild(c2);
			a.style.visibility="visible";
		});
	});
}
function f_tchat_ok(f,t,g){
	jQuery(document).ready(function(){
		jQuery.post(g,{'tchat':'tchatOk','fm':f,'to':t},function(r){
			f_tchat(f,t,g,0,ps);
			document.getElementById('inTchat').disabled=false;
		});
	});
}
function f_tchat_on(){
	document.getElementById('inTchat').disabled=false;
	var c2=document.createElement("div");
	c2.className="az";
	c2.innerHTML=rencobjet.tchat_dem_ok;
	document.getElementById('contenu').appendChild(c2);
}
function f_tchat_off(){
	var c2=document.createElement("div"),c=document.getElementById('contenu'),u=navigator.userAgent.toLowerCase(),sm=u.indexOf("android")>-1;
	c2.className="az";
	c2.innerHTML=rencobjet.ferme_fenetre;
	if(sm)c.insertBefore(c2,c.firstChild);
	else c.appendChild(c2);
	clearInterval(scrute);
	document.getElementById('inTchat').disabled=true;
	document.getElementById('bcam').onclick=function(){
		return true;
	};
	if(!sm)c.scrollTop=c.scrollHeight;
}
function f_tchat_envoi(f,t,h,g){
	jQuery(document).ready(function(){
		jQuery.post(g,{'tchat':'tchatEnvoi','fm':f,'to':t,'msg':h},function(r){
			f_tchat_actualise(h,r,f,t);
		});
	});
}
function f_tchat_actualise(h,r,f,t){
	var u=navigator.userAgent.toLowerCase(),sm=u.indexOf("android")>-1,c=document.getElementById('contenu');
	h=f_emot(h);
	r=f_emot(r);
	if(r){
		r1=r.split('['+t+']');
		if(r1!=null){
			for(v=0;v<r1.length;v++){
				if(r1[v].length>0&&r1[v]!="-"){
					if(r1[v].charCodeAt(r1[v].length-1)==127){
						r1[v]=r1[v].substr(0,r1[v].length-1);
						var b0=document.getElementById('bcam');
						if(b0.className!="cam"){
							b0.className="cam";
							b0.title="webcam";
							b0.onclick=function(){
								if(moi!=''){
									moi='';f_camOff();
								}
								else webcam(f,t);
							};
							if(document.getElementById('rencCam')===null){
								var a=document.getElementById('rencTchat'),b=document.createElement("div");
								b.id="rencCam2";
								b.className="rencCam2";
								a.parentNode.insertBefore(b,a.nextSibling);
								b=document.createElement("div");
								b.id="rencCam";
								b.className="rencCam";
								a.parentNode.insertBefore(b,a.nextSibling);
							}
						}
					}
					var c2=document.createElement("div");
					c2.className="to";
					c2.innerHTML="<b>"+ps+"</b><br />"+r1[v];
					if(sm)c.insertBefore(c2,c.firstChild);
					else c.appendChild(c2);
					cd.playclip();
				}
			}
		}
	}
	if(h){
		var c1=document.createElement("div");
		c1.className="fm";
		c1.innerHTML=h;
		if(sm)c.insertBefore(c1,c.firstChild);
		else c.appendChild(c1);
	}
	if(!sm)c.scrollTop=c.scrollHeight;
}
function f_tchat_fin(f,t,g){
	jQuery(document).ready(function(){
		clearInterval(scrute);
		jQuery.post(g,{'tchat':'tchatFin','fm':f,'to':t},function(r){
			veille=setInterval('f_tchat_veille();',5111);
		});
	});
	var a=document.getElementById('rencTchat');
	a.innerHTML="";
	a.style.visibility="hidden";
	if(moi!=''){
		moi='';
		f_camOff();
	}
}
/* Webcam */
function webcam(f,t){
	moi=f+"-"+t;
	toi=t+"-"+f;
	var sw=rencobjet.ajaxchat.substr(0,rencobjet.ajaxchat.length-19)+"cam.swf",cible=document.getElementById('rencCam2'),run=3;
	cible.style.visibility="visible";
	var source='<object id="rencCamObj" type="application/x-shockwave-flash" data="'+sw+'" width="300" height="225"><param name="movie" value="'+sw+'" /><param name="allowScriptAccess" value="always" /></object>';
	cible.innerHTML=source;
	stream_on();
	(_register=function(){
		var cam=document.getElementById('rencCamObj');
		if(cam&&cam.capture!==undefined){
			webcam.capture=function(x){
				return cam.capture(x);
			};
			webcam.turnOff=function(){
				return cam.turnOff();
			};
			webcam.onSave=function(x){
				saveData(x)
			};
		}
		else if(run==0){
			cam.parentNode.removeChild(cam);
			cible.style.visibility="hidden";
			document.getElementById('rencCam').style.visibility="visible";
		}
		else{
			run--;
			window.setTimeout(_register, 1000);
		}
	})();
}
function f_camOk(f){
	var so=document.getElementById('rencCamObj'),de2=document.getElementById('rencCam2');
	so.width=160;
	so.height=120;
	de2.style.width="160px";
	de2.style.height="120px";
	de2.style.bottom="245px";
	document.getElementById('rencCam').style.visibility="visible";
	webcam.capture();
}
function f_camOff(f){
	var so=document.getElementById('rencCamObj'),de=document.getElementById('rencCam'),de2=document.getElementById('rencCam2'),ig=document.getElementById('rencCamImg');
	clearInterval(vue);
	if(so!=null)so.turnOff();
	de.removeChild(ig);
	de.style.visibility="hidden";
	de2.style.width="300px";
	de2.style.height="225px";
	de2.style.bottom="10px";
	de2.style.visibility="hidden";
}
function saveData(data){
	if(rs==1){
		rs=0;
		var s="tchat=cam&id="+moi+"&image="+data,a=new XMLHttpRequest();
		a.open("POST",rencobjet.ajaxchat,true);
		a.setRequestHeader('Content-Type',"application/x-www-form-urlencoded; charset=UTF-8");
		a.setRequestHeader("X-Requested-With","XMLHttpRequest");
		a.setRequestHeader("Content-length",s.length);
		a.onreadystatechange=function(){
			if(a.readyState==4)rs=1;
		};
		a.send(s);
	}
}
function stream_cam(){
	document.getElementById('rencCamImg').src=rencUrl+'/wp-content/uploads/tchat/cam'+toi+'.jpg?'+new Date().getTime();
}
function stream_on(){
	var b2=document.createElement("img");
	b2.id="rencCamImg";
	b2.src="";
	document.getElementById('rencCam').appendChild(b2);
	vue=setInterval('stream_cam();',1000);
}
//
if(document.getElementById("infoChange")!==null)window.setTimeout('document.getElementById("infoChange").innerHTML=""',3000);
jQuery(document).ready(function(){
	if(rencobjet.tchaton==1)veille=setInterval('f_tchat_veille();',5111);
});
