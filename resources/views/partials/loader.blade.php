<div class="preloader">

  <div class="logo"><img src="@asset('images/logo-primary.png')" width="200px" alt="" /></div>

</div>

<style>

  .preloader {
    position: fixed;
    z-index: 999;
    transition: ease-out 0.1s;
    height: 100vh;
    width: 100vw;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
  }

  .preloader.is-loaded {
    pointer-events: none;
    opacity: 0;
  }

</style>
