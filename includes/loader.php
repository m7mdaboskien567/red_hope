<section class="loading-page">
    <div class="loading-page-content">
        <div id="overlay">
            <div class="loader-logo-wr">
                <div class="logo logo-color" id="logoColor"></div>
                <div class="logo logo-gray" id="logoGray"></div>
            </div>
        </div>
        <div class="load-progress" id="loadProgress"></div>
    </div>
</section>

<script>
    window.addEventListener('load', () => {
    const loadingPage = document.querySelector('.loading-page');
    const loadProgress = document.getElementById('loadProgress');
    const logoColor = document.getElementById('logoColor');

    if (!loadingPage || !loadProgress || !logoColor) {
        if (loadingPage) loadingPage.style.display = 'none';
        return;
    }

    setTimeout(() => {
        loadProgress.style.width = '100%';
        logoColor.style.height = '250px';
    }, 100); 

    setTimeout(() => {
        loadingPage.style.transition = 'transform 1s cubic-bezier(0.86, 0, 0.07, 1)';
        loadingPage.style.transform = 'translateY(-100%)';
        
        document.dispatchEvent(new CustomEvent('pageLoaded'));
        
        setTimeout(() => {
            loadingPage.style.display = 'none';
        }, 1000);
    }, 1900); 
});

</script>