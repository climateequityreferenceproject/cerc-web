<!--
Credit for code: Mat Swainson https://medium.com/front-end-weekly/creating-a-dismissible-banner-component-ad02493b1cc2

Usage: create a <div> for each dismissable banner using the template below (insert the divs above the <script> tag).
The "data-type" defines the styling of the banner. permitted values: [empty], info, success, error, warning
"data-value" contains the html string to be displayed inside the banner.

<div
	data-component="dismissible-item"
	data-type="info"
	data-value="<strong>Welcome to the site.</strong>"
></div>

--->



<script>!function(t){var e=function(e,a,n){var i="<span>"+n+' <button type="button" class="close">X</button></span>';e.removeAttribute("data-component"),e.removeAttribute("data-value"),e.removeAttribute("data-type"),e.classList.add("dismissible","dismissible-"+a),e.innerHTML=i,e.querySelector(".close").addEventListener("click",function(a){var n=e.offsetHeight,i=1,r=null;function l(){n-=2,e.setAttribute("style","height: "+n+"px; opacity: "+i),n<=0&&(t.clearInterval(r),r=null,e.remove())}r=t.setInterval(function(){i-=.1,e.setAttribute("style","opacity: "+i),i<=0&&(t.clearInterval(r),r=t.setInterval(l,1))},25)})},a=Array.prototype.slice.call(document.querySelectorAll('[data-component="dismissible-item"]'));if(a.length)for(var n=0;n<a.length;n++){var i=a[n].getAttribute("data-type"),r=a[n].getAttribute("data-value");new e(a[n],i,r)}}(window);</script>
<style>.dismissible{color:#222;line-height:22px;position:relative;overflow:hidden}.dismissible span{background:#fff;border:2px solid rgba(0,0,0,.3);border-radius:5px;box-shadow:0 0 20px 0 rgba(0,0,0,.2);box-sizing:border-box;display:block;margin:0 auto 20px auto;padding:10px 35px 10px 10px;position:relative}.dismissible button{background:rgba(0,0,0,.4);border:0;border-radius:100%;color:#fff;cursor:pointer;font-weight:700;height:24px;line-height:20px;margin-top:-12px;right:10px;position:absolute;top:50%;width:24px}.dismissible button:focus{outline:0}.dismissible-error span{background-color:#ff5252;border-color:#ff1744;color:#fff}.dismissible-error button{background-color:#d50000;color:#ffcdd2}.dismissible-info span{background-color:#0271bc;border-color:#6b87c3;color:#e3f2fd}.dismissible-info button{background-color:#2196f3;color:#e3f2fd}.dismissible-success span{background-color:#40822e;border-color:#7cb342;color:#fff}.dismissible-success button{background-color:#7cb342;color:#dcedc8}.dismissible-warning span{background-color:#ffa726;border-color:#fb8c00;color:#fff}.dismissible-warning button{background-color:#fb8c00;color:#ffe0b2}</style>
