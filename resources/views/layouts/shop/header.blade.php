<!-- HEADER -->
<header>
    <!-- TOP HEADER -->
    <div id="top-header">
        <div class="container">
            <ul class="header-links pull-left  desktop-only">
                <li><a href="#"><i class="fa fa-phone"></i> {{ $companySettings->company_phone ?? '' }}</a></li>
                <li><a href="#"><i class="fa fa-envelope-o"></i> {{ $companySettings->company_email ?? '' }}</a></li>
                <li><a href="#"><i class="fa fa-map-marker"></i> {{ $companySettings->company_address ?? '' }}</a></li>
            </ul>
            <ul class="header-links pull-right">
                @auth
                <li class="dropdown user-dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-user"></i> Hi, {{ Auth::user()->username }} <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('b2b.profile.index') }}"><i class="fa-regular fa-address-card"></i> Profile</a></li>
                        <li><a href="{{ route('b2b.address.index') }}"> <i class="fa-solid fa-map-location-dot"></i> My Address</a></li>
                        <li role="separator" class="divider"></li>
                        <li>
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa-solid fa-right-from-bracket"></i> Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
                @else
                <li><a href="{{ route('login') }}"><i class="fa fa-sign-in"></i> Sign-In</a></li>
                @endauth

            </ul>
        </div>
    </div>
    <!-- /TOP HEADER -->

    <!-- MAIN HEADER -->
    <div id="header">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <!-- LOGO -->
                <div class="col-md-4">
                    <div class="header-logo">
                        <a href="#" class="logo">
                            <img src="{{ asset($companySettings->company_logo  ?? 'assets/dashboard/images/noimage.png'  ) }}" alt="" width="70">
                        </a>
                    </div>
                </div>
                <!-- /LOGO -->

                <!-- SEARCH BAR -->
                <div class="col-md-4">
                    <div class="header-search  {{ Route::is('welcome') ||  Route::is('home') ? '' : 'd-none' }}">
                        <form>
                            <!-- <select class="input-select">
                                <option value="0">All Categories</option>
                                <option value="1">Category 01</option>
                                <option value="1">Category 02</option>
                            </select> -->
                            <input class="input" placeholder="Search here" id="search_value">
                            <button class="search-btn" id="search-btn">Search</button>
                        </form>
                    </div>
                </div>
                <!-- /SEARCH BAR -->

                <!-- ACCOUNT -->
                @auth
                <div class="col-md-4 clearfix">
                    <div class="header-ctn">

                        <div>
                            <a href="{{ route('home') }}">
                                <i class="fa-solid fa-home"></i>
                                <span>Home</span>
                                <!-- <div class="qty">2</div> -->
                            </a>
                        </div>

                        <div>
                            <a href="{{ route('b2b.delivery.index') }}">
                                <i class="fa-solid fa-truck"></i>
                                <span>Delivery</span>
                                <!-- <div class="qty">2</div> -->
                            </a>
                        </div>

                        <div>
                            <a href="{{ route('b2b.quotations.review') }}">
                                <i class="fa-solid fa-receipt"></i>
                                <span>Quotation</span>
                                @if( $sentQuotationCount > 0 )
                                <div class="qty">{{ $sentQuotationCount }}</div>
                                @endif
                            </a>
                        </div>

                        <div class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                <i class="fa-solid fa-box"></i>
                                <span>PR</span>
                                @if($pendingRequestCount > 0)
                                <div class="qty" id="purchase-request-count">{{ $pendingRequestCount }}</div>
                                @else
                                <div class="qty d-none" id="purchase-request-count">0</div>
                                @endif
                            </a>
                            <div class="cart-dropdown">
                                <div class="cart-list" id="cart-list">
                                    <!-- Product widgets will be injected here -->
                                </div>

                                <div class="cart-summary">
                                    <small id="cart-total-quantity">0 Item(s) selected</small>
                                    <h5 id="cart-subtotal">GRAND TOTAL: $0.00</h5>
                                </div>

                                <div class="cart-btns p-1 {{ Route::is('b2b.purchase-requests.index') ? 'd-none' : '' }}">
                                    <a href="{{ route('b2b.purchase-requests.index') }}" style="font-size:12px;width:100%">View All</a>
                                </div>
                            </div>

                        </div>



                        <!-- Menu Toogle -->
                        <div class="menu-toggle">
                            <a href="#">
                                <i class="fa fa-bars"></i>
                                <span>Menu</span>
                            </a>
                        </div>
                        <!-- /Menu Toogle -->
                    </div>
                </div>
                @endauth
                <!-- /ACCOUNT -->
            </div>
            <!-- row -->
        </div>
        <!-- container -->
    </div>
    <!-- /MAIN HEADER -->
</header>
<!-- /HEADER -->