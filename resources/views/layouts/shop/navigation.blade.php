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

            @if (Route::is('home') || Route::is('welcome') )
            <ul class="main-nav nav navbar-nav">
                <li><a href="#" class="category-btn" data-id="">All</a></li>
                @foreach($categories as $category)
                <li><a href="#" class="category-btn" data-id="{{ $category->id }}">{{ $category->name }}</a></li>
                @endforeach
            </ul>
            @elseif (Route::is('purchase-requests.index'))
            <ul class="main-nav nav navbar-nav">
                <!-- <li class="filter-btn active" data-status=""><a href="#">All</a></li> -->
                <li class="filter-btn active" data-status="pending"><a href="#">Pending</a></li>
                <li class="filter-btn" data-status="quotation_sent"><a href="#">Quotation Sent</a></li>
                <li class="filter-btn" data-status="po_submitted"><a href="#">PO Submitted</a></li>
                <li class="filter-btn" data-status="so_created"><a href="#">Sales Order Created</a></li>
                <li class="filter-btn" data-status="delivery_in_progress"><a href="#">Delivery In Progress</a></li>
                <li class="filter-btn" data-status="delivered"><a href="#">Delivered</a></li>
                <li class="filter-btn" data-status="invoice_sent"><a href="#">Invoice Sent</a></li>
            </ul>
            @endif

            @else

            <ul class="main-nav nav navbar-nav">
                <li><a href="#" class="category-btn" data-id="">All</a></li>
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