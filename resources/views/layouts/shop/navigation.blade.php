<!-- NAVIGATION -->
<nav id="navigation">
    <!-- container -->
    <div class="container">
        <!-- responsive-nav -->
        <div id="responsive-nav">
            <!-- NAV -->
            @auth

            <!-- <li class="active"><a href="#">Home</a></li> -->
            <!-- <li><a href="#">Hot Deals</a></li>
                <li><a href="#">Categories</a></li>
                <li><a href="#">Laptops</a></li>
                <li><a href="#">Smartphones</a></li>
                <li><a href="#">Cameras</a></li>
                <li><a href="#">Accessories</a></li> -->

            <ul class="main-nav nav navbar-nav">
                <li class="active"><a href="#" class="category-btn" data-id="">All</a></li>
                @foreach($categories as $category)
                <li><a href="#" class="category-btn" data-id="{{ $category->id }}">{{ $category->name }}</a></li>
                @endforeach
            </ul>

            @endauth
            <!-- /NAV -->
        </div>
        <!-- /responsive-nav -->
    </div>
    <!-- /container -->
</nav>
<!-- /NAVIGATION -->