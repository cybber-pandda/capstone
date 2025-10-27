<!-- HEADER -->
<header>
    <!-- TOP HEADER -->
    <div id="top-header" class="top-header-row">
        <div class="container">
            <ul class="header-links pull-left desktop-only no-link">
                <li><a><i class="fa fa-phone"></i> {{ $companySettings->company_phone ?? '' }}</a></li>
                <li class="removeAddressinTablet"><a><i class="fa fa-envelope"></i> {{ $companySettings->company_email ?? '' }}</a></li>
                <li class="removeEmailinTablet"><a><i class="fa fa-map-marker"></i> {{ $companySettings->company_address ?? '' }}</a></li>
            </ul>
                <ul class="header-links pull-right">
                    @auth
                    <li class="dropdown user-dropdown">
                        <div style="display: flex; justify-content:space-between; align-items:center; gap:10px;">
                            <div>
                                <a href="{{ route('notification.index') }}" 
                                title="Notifications" 
                                style="color:white; text-decoration:none; position:relative; display:inline-block; margin-right:10px;">
                                    <i class="fa-solid fa-bell"></i>
                                    @if(isset($notificationCount) && $notificationCount > 0)
                                        <span class="notification-pulse"></span>
                                        <span class="notification-number">{{ $notificationCount }}</span>
                                    @endif
                                </a>                         

                                <a href="{{ route('chat.index') }}"
                                title="Messages"
                                style="color:white; text-decoration:none; margin-right:10px;">
                                    <i class="fa fa-message"></i>
                                </a>

                                <a href="#" class="dropdown-toggle"
                                title="Profile"
                                data-toggle="dropdown" 
                                role="button" 
                                aria-haspopup="true" 
                                aria-expanded="false"
                                style="color:white; text-decoration:none; margin-right:10px;">
                                    <i class="fa fa-user"></i> Hi, {{ Auth::user()->username }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu">
                                    <li><a href="{{ route('b2b.profile.index') }}"><i class="fa-regular fa-address-card"></i> Profile</a></li>
                                    <li><a href="{{ route('b2b.address.index') }}"> <i class="fa-solid fa-map-location-dot"></i> My Address</a></li>
                                    <li><a href="{{ route('b2b.purchase.index') }}"> <i class="fa-solid fa-bag-shopping"></i> My Purchase</a></li>
                                    <li><a href="{{ route('b2b.purchase.order') }}"> <i class="fa-solid fa-basket-shopping"></i> My Purchase Order</a></li>
                                    <li><a href="{{ route('b2b.purchase.credit') }}"> <i class="fa-solid fa-credit-card"></i> My Credit</a></li>
                                    <li><a href="{{ route('b2b.purchase.rr') }}"><i class="fa-solid fa-right-left"></i> Return/Refund Items</a></li>
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
                            </div>

                            <div style="display:flex; align-items:center; gap:6px; white-space:nowrap;">
                                <span id="showLimitForMobile" style="color:white;">
                                    Credit Limit: {{ number_format(Auth::user()->credit_limit, 2) }}   
                                    <a href="javascript:void(0);" id="refreshLimitBtn" title="Refresh" 
                                    style="color:white; text-decoration:none; font-size:15px;">
                                        <i class="fa-solid fa-rotate-right"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
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
            <div class="row header-row" id="hideHeaderFormobile">
                <!-- LOGO -->
                <div class="col-md-4">
                    <div class="header-logo">
                        <a href="{{ route('home') }}" class="logo">
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
                            <!-- nag add ako ng search input para sa mobile -->
                            <input class="input" placeholder="Search here" id="search_value_mobile" style = "display:none !important; @media (max-width: 767.98px) {
                display: block !important;">
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
                            <a href="{{ route('home') }}" class="{{ Route::is('home') ? 'active-icon' : '' }}">
                                <i class="fa-solid fa-home"></i>
                                <span>Home</span>
                                <!-- <div class="qty">2</div> -->
                            </a>
                        </div>

                        <div>
                            <a href="{{ route('b2b.delivery.index') }}" class="{{ Route::is('b2b.delivery.index') ? 'active-icon' : '' }}" style="position: relative;">
                                <i class="fa-solid fa-truck"></i>
                                <span>Delivery</span>

                                @if(isset($deliveryCount) && $deliveryCount > 0)
                                    <div class="qty">
                                        {{ $deliveryCount }}
                                    </div>
                                @endif
                            </a>
                        </div>


                        <div>
                            <a href="{{ route('b2b.quotations.review') }}" class="{{ Route::is('b2b.quotations.review') ? 'active-icon' : '' }}">
                                <i class="fa-solid fa-receipt"></i>
                                <span>Quotation</span>
                                @if( $sentQuotationCount > 0 )
                                <div class="qty">{{ $sentQuotationCount }}</div>
                                @endif
                            </a>
                        </div>

                        <div class="dropdown">
                            <a class="dropdown-toggle {{ Route::is('b2b.purchase-requests.index') ? 'active-icon' : '' }}   " data-toggle="dropdown" aria-expanded="true" style="cursor: pointer;">
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

            <!-- row -->
            <div class="row" id="showHeaderFormobile">

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

            </div>
            <!-- row -->
        </div>
        <!-- container -->
    </div>
    <!-- /MAIN HEADER -->
</header>
<!-- /HEADER -->

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const refreshBtn = document.getElementById('refreshLimitBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                const icon = this.querySelector('i');
                icon.classList.add('fa-spin');
                location.reload();
            });
        }
    });
</script>
@endpush