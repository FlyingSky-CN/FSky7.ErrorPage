/**
 * Dark Mode
 * on MDUI
 * 
 * @author FlyingSky
 * @version 1.0
 */
console.log("\n %c 👀 MDUI Dark Mode %c \n","color:#fff;background:#444;padding:5px 0;border: 1px solid #444;","");

var $$ = mdui.JQ;

if (typeof(onDarkMode) != 'function') {
    function onDarkMode() {
        var body = $$('body'),
            appbar = $$('.mdui-appbar'),
            meta = document.getElementsByTagName('meta');
        console.log('Dark mode on');
        document.cookie = "dark=1;path=/;domain=fsky7.com";
        body.addClass('mdui-theme-layout-dark');
        body.removeClass('mdui-theme-accent-blue');
        body.addClass('mdui-theme-accent-light-blue');
        appbar.css('background-color', '#212121');
        meta["theme-color"].setAttribute('content','#212121');
    }
}
if (typeof(offDarkMode) != 'function') {
    function offDarkMode() {
        var body = $$('body'),
            appbar = $$('.mdui-appbar'),
            meta = document.getElementsByTagName('meta');
        console.log('Dark mode off');
        document.cookie = "dark=0;path=/;domain=fsky7.com";
        body.removeClass('mdui-theme-layout-dark');
        body.removeClass('mdui-theme-accent-light-blue');
        body.addClass('mdui-theme-accent-blue');
        appbar.css('background-color', '#ffffff');
        meta["theme-color"].setAttribute('content','#FFFFFF');
    }
}

/* Dark Mode 的控制（系统黑暗模式优先于 Cookie 中的黑暗模式） */
function switchDarkMode() {
	/* 手动触发 */
	var night = document.cookie.replace(/(?:(?:^|.*;\s*)dark\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
	if (night == '0'){
		onDarkMode();
		mdui.snackbar({message: '已开启 Dark Mode ，早 6 点之前保持开启。',position: 'right-bottom',timeout: 1000});
	}else{
		offDarkMode();
		mdui.snackbar({message: '已关闭 Dark Mode ',position: 'right-bottom',timeout: 1000});
	}
}
(function(){
	/* 加载完触发，判断时间段（当系统开启黑暗模式时不执行） */
	if (getComputedStyle(document.documentElement).getPropertyValue('content') != '"dark"') {
		if(document.cookie.replace(/(?:(?:^|.*;\s*)dark\s*\=\s*([^;]*).*$)|^.*$/, "$1") === ''){
			if(new Date().getHours() > 22 || new Date().getHours() < 6){
				onDarkMode();
			}else{
				offDarkMode();
			}
		}else{
			var dark = document.cookie.replace(/(?:(?:^|.*;\s*)dark\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
			if(dark == '0'){
				offDarkMode();
			}else if(dark == '1'){
				onDarkMode();
			}
		}
	}
})();
document.addEventListener('visibilitychange', function () {
	/* 切换标签页时触发 */
	var dark = document.cookie.replace(/(?:(?:^|.*;\s*)dark\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
	if(dark == '0'){
		offDarkMode();
		if (getComputedStyle(document.documentElement).getPropertyValue('content') == '"dark"') {
			onDarkMode();
			mdui.snackbar({message: '已开启 Dark Mode ，跟随系统。',position: 'right-bottom',timeout: 1000});
		};
	}else if(dark == '1'){
		onDarkMode();
	}
});
if (getComputedStyle(document.documentElement).getPropertyValue('content') == '"dark"') {
	var dark = document.cookie.replace(/(?:(?:^|.*;\s*)dark\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
	/* 加载完触发，判断系统黑暗模式是否开启 */
	if (dark == '0') {
		onDarkMode();
		mdui.snackbar({message: '已开启 Dark Mode ，跟随系统。',position: 'right-bottom',timeout: 1000});
	}
};
window.matchMedia('(prefers-color-scheme: dark)').addEventListener("change",(e) => {
	/* 系统黑暗模式切换时触发 */
	if (e.matches) {
		onDarkMode();
		mdui.snackbar({message: '已开启 Dark Mode ，跟随系统。',position: 'right-bottom',timeout: 1000});
	} else {
		var night = document.cookie.replace(/(?:(?:^|.*;\s*)dark\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
		if (night == '1') {
			offDarkMode();
			mdui.snackbar({message: '已关闭 Dark Mode ',position: 'right-bottom',timeout: 1000});
		}
	}
});
