<!doctype html>
<html lang="{$CurrentLocale.Lang}">
<head>
  <meta name='viewport' content='width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no'>
  <meta charset='utf-8'>
    {asset name="Head"}
  <script src="/js/fontfaceobserver.js"></script>
  <link rel="stylesheet" href="/css/bundle.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,700|Roboto:400,400italic,700,700italic">
  <script>
    if (sessionStorage.getItem('fonts-loaded')) {
      document.documentElement.classList.add('fonts-loaded')
    } else {
      var roboto = new FontFaceObserver('Roboto')
      var slab   = new FontFaceObserver('Roboto Slab')
      Promise.all([roboto.load(), slab.load()]).then(function() {
        document.documentElement.classList.add('fonts-loaded')
        sessionStorage.setItem('fonts-loaded', true)
      })
    }
  </script>
  <style>
    .DismissMessage {
      text-align: center;
    }

    .SplashInfo {
      margin: 0 auto;
    }

  </style>
</head>
<body id="{$BodyID}">
{literal}
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-5WRJXR"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5WRJXR');</script>
<!-- End Google Tag Manager -->

<header>
    <div class="z9001 fixed t0 w100 bg-white--transparent slab" role="banner">
      <div class="flex flex--justify-between relative container overflow-hidden">
        <div class="ml0 mt0.5 mb0.5 mr2" style="max-width: 128px;">
          <a href="/" class="brand"><img src="/images/tt_logo.svg" alt="Telltale"></a>
        </div>
        <nav class="dn flex@lg flex--align-center">
          <ul class="nav nav--horizontal nav--gutters">
              <li><a href="/series" class="nav-item">all series</a></li><li><a href="/community" class="nav-item is-active">community</a></li><li><a href="/news" class="nav-item">news</a></li><li><a href="/support" class="nav-item">support</a></li><li><a href="/company" class="nav-item">company</a></li><li><a href="/jobs" class="nav-item">jobs</a></li><li><a href="/store" class="nav-item">store</a></li>
          </ul>
        </nav>

        <div class="dn flex@lg overflow-hidden ml-auto">
          <div class="flex flex--align-center">
            <a class="menubar-icon mr2 pointer" href="#">
              <img src="/images/icons/cart.png" class="js-cart-icon" alt="">
            </a>
            <a href="/account/" class="button bg-white button--xs">my account</a>
          </div>
        </div>

        <div class="dn@lg flex flux--justify-end items-center ml-auto">
          <a href="/cart" class="block pointer js-cart-icon mr3">
            <span class="sr-only">cart</span>
            <img src="/images/icons/cart.png" class="block" alt="" width="26" height="26">
          </a>
          <a href="/account/" class="block pointer mr3">
            <span class="sr-only">my account</span>
            <img src="/images/icons/profile.png" class="block" alt="" width="26" height="20">
          </a>
          <a href="#" class="block pointer js-hamburger">
            <span class="sr-only">menu</span>
            <img src="/images/icons/hamburger.png" class="block" alt="" width="26" height="20">
          </a>
        </div>
      </div>

      <nav class="mobile-nav" role="nav">
        <div class="container">
          <ul class="nav">
            <li><a href="/series" class="nav-item block p1">all series</a></li>
            <li><a href="/community/" class="nav-item block p1">community</a></li>
            <li><a href="/news" class="nav-item block p1">news</a></li>
            <li><a href="/support" class="nav-item block p1">support</a></li>
            <li><a href="/jobs" class="nav-item block p1">jobs</a></li>
            <li><a href="/company" class="nav-item block p1">company</a></li>
            <li><a href="/store" class="nav-item block p1">store</a></li>
            <li><a href="/cart" class="js-cart-icon nav-item block p1">cart</a></li>
          </ul>
        </div>
      </nav>
    </div>
</header>
{/literal}

<main class="site__content">
  <div id="Content" class="Content mt5">
{asset name="Content"}
</div>
</main>
{literal}
<footer class="global-footer">
  <div class="container">
    <div class="flex flex--align-center flex--wrap">
      <a href="/" class="brand brand--white mr3" style="max-width: 128px;"><svg viewBox="0 0 120 24"><path d="M12 8.8l2.3-.1v5.5L12 14v8.9H5V14l-1.7.2V8.7l1.7.1V4h7v4.8zM22.2 17c0 .7.1 1.3.3 1.8.2.5.7.7 1.3.7.4 0 .7-.1.9-.4.3-.2.4-.5.5-.8H32c-.2.8-.6 1.6-1.1 2.2-.6.6-1.2 1.2-2 1.6-.8.4-1.6.7-2.4.9s-1.7.3-2.5.3c-1.1 0-2.2-.2-3.3-.5s-2-.7-2.8-1.4c-.8-.6-1.5-1.4-1.9-2.3-.5-.9-.7-2-.7-3.2 0-1.1.2-2.2.6-3.2.4-1 1-1.8 1.8-2.4s1.6-1.2 2.6-1.6c1-.4 2.1-.5 3.2-.5 1.2 0 2.4.2 3.4.6 1.1.4 2 .9 2.8 1.6.8.7 1.4 1.6 1.9 2.5s.7 2.1.7 3.3c0 .1 0 .2-.1.3v.3h-10v.2zm3.3-3c0-.5-.2-.9-.5-1.3s-.7-.6-1.2-.6-.9.2-1.1.5-.4.8-.4 1.4h3.2zM34.8 1h7v21.9h-7V1zM44.9 1h7v21.9h-7V1zM62.7 8.8l2.3-.1v5.5l-2.3-.2v8.9h-7V14l-1.7.2V8.6l1.7.1V3.9h7v4.9zM76.6 21.1h-.2c-.7.8-1.4 1.3-2 1.7-.6.4-1.4.5-2.3.5s-1.8-.2-2.5-.6c-.8-.4-1.4-1-1.9-1.7s-.9-1.5-1.2-2.4-.5-1.9-.5-2.7c0-.9.2-1.8.4-2.7s.7-1.7 1.2-2.4 1.2-1.3 2-1.8c.8-.4 1.7-.6 2.7-.6 1.8 0 3.2.7 4.2 2h.2l-.2-1.7h6.8V23h-6.8l.1-1.9zm-3.3-3.8c.3.4.8.6 1.4.6.6 0 1.1-.2 1.4-.6.3-.4.5-.9.5-1.6 0-.6-.2-1-.5-1.4-.4-.4-.8-.5-1.4-.5-.5 0-1 .2-1.3.6-.3.4-.5.8-.5 1.4-.1.6.1 1.1.4 1.5zM86.1 1h7v21.9h-7V1zM102.4 17c0 .7.1 1.3.3 1.8.2.5.7.7 1.3.7.4 0 .7-.1.9-.4.3-.2.4-.5.5-.8h6.8c-.2.8-.6 1.6-1.1 2.2-.6.6-1.2 1.2-2 1.6-.8.4-1.6.7-2.4.9-.8.2-1.7.3-2.5.3-1.1 0-2.2-.2-3.3-.5-1-.3-2-.7-2.8-1.4-.8-.6-1.5-1.4-1.9-2.3-.5-.9-.7-2-.7-3.2 0-1.1.2-2.2.6-3.2.4-1 1-1.8 1.8-2.4s1.6-1.2 2.6-1.6c1-.4 2.1-.5 3.2-.5 1.2 0 2.4.2 3.4.6 1.1.4 2 .9 2.8 1.6.8.7 1.4 1.6 1.9 2.5.5 1 .7 2.1.7 3.3 0 .1 0 .2-.1.3v.3h-10v.2zm3.3-3c0-.5-.2-.9-.5-1.3s-.7-.6-1.2-.6-.9.2-1.1.5c-.3.4-.4.8-.4 1.4h3.2z"/><path d="M112.8 4.6c1.2 0 2.2 1 2.2 2.2S114 9 112.7 9c-1.3 0-2.3-1-2.3-2.2s1.1-2.2 2.4-2.2zm-.1.4c-1 0-1.7.8-1.7 1.8s.7 1.8 1.7 1.8 1.7-.8 1.7-1.7c0-1.1-.7-1.9-1.7-1.9zm-.3 2.9h-.5V5.7c.2 0 .5-.1.8-.1.4 0 .6.1.8.2.1.1.2.3.2.5 0 .3-.2.4-.5.5.2.1.3.2.4.5.1.3.1.5.2.5h-.5c-.1-.1-.1-.3-.2-.5 0-.2-.2-.3-.5-.3h-.2v.9zm0-1.2h.2c.3 0 .5-.1.5-.3 0-.2-.1-.3-.5-.3h-.3v.6z"/></svg></a>

      <nav>
        <ul class="nav nav--horizontal nav--gutters">
          <li><a href="https://www.telltale.com/series" class="dropdown-toggle">all series</a></li>
          <li><a href="https://community.telltale.com" class="">community</a></li>
          <li><a href="https://www.telltale.com/news" class="">news</a></li>
          <li><a href="https://www.telltale.com/support" class="">support</a></li>
        </ul>
      </nav>

      <div class="global-footer__social">
        <a href="https://www.facebook.com/telltalegames/" class="social-icon js-external">
          <img src="/images/facebook.png" alt="facebook" width="64" height="64">
        </a>
        <a href="https://twitter.com/telltalegames" class="social-icon js-external">
          <img src="/images/twitter.png" alt="twitter" width="64" height="64">
        </a>
        <a href="https://www.youtube.com/user/TelltaleGames" class="social-icon js-external">
          <img src="/images/youtube.png" alt="youtube" width="64" height="64">
        </a>
        <a href="https://www.pinterest.com/telltalegames/" class="social-icon js-external">
          <img src="/images/pinterest.png" alt="pinterest" width="64" height="64">
        </a>
        <a href="https://telltalegames.tumblr.com/" class="social-icon js-external">
          <img src="/images/tumblr.png" alt="tumblr" width="64" height="64">
        </a>
      </div>
    </div>

    <div class="global-footer__legal">
      <p class="text-legal">&copy; 2026 Telltale, Incorporated. All rights reserved. <a href="/tos">Terms of Service</a> / <a href="/privacy-policy">Privacy Policy</a></p>
    </div>
  </div>
</footer>
<!--  v.2.6 -->

{/literal}
{event name="AfterBody"}
<script src="/js/commons.bundle.js"></script>
</body>
</html>
