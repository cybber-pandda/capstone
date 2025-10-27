<!-- FOOTER -->
<footer id="footer">
    <!-- top footer -->
    <div class="section address-map-view">
        <!-- container -->
        <div class="container-fluid">

            <div style="overflow:hidden;max-width:100%;width:100%;height:300px;">
                <div id="embedded-map-display" style="height:100%; width:100%;max-width:100%;"><iframe style="height:100%;width:100%;border:0;" frameborder="0" src="https://www.google.com/maps/embed/v1/place?q=National+Highway,+Barangay+Balubal,+Sariaya,+4322,+Quezon+Province,+Philippines&key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8"></iframe></div><a class="google-map-html" href="https://www.bootstrapskins.com/themes" id="auth-map-data">premium bootstrap themes</a>
                <style>
                    #embedded-map-display img {
                        max-width: none !important;
                        background: none !important;
                        font-size: inherit;
                        font-weight: inherit;
                    }
                </style>
            </div>
            <!-- row -->
            <!-- <div class="row">
                <div class="col-md-3 col-xs-6">
                    <div class="footer">
                        <h3 class="footer-title">About Us</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut.</p>
                        <ul class="footer-links">
                            <li><a href="#"><i class="fa fa-map-marker"></i>1734 Stonecoal Road</a></li>
                            <li><a href="#"><i class="fa fa-phone"></i>+021-95-51-84</a></li>
                            <li><a href="#"><i class="fa fa-envelope-o"></i>email@email.com</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-3 col-xs-6">
                    <div class="footer">
                        <h3 class="footer-title">Categories</h3>
                        <ul class="footer-links">
                            <li><a href="#">Hot deals</a></li>
                            <li><a href="#">Laptops</a></li>
                            <li><a href="#">Smartphones</a></li>
                            <li><a href="#">Cameras</a></li>
                            <li><a href="#">Accessories</a></li>
                        </ul>
                    </div>
                </div>

                <div class="clearfix visible-xs"></div>

                <div class="col-md-3 col-xs-6">
                    <div class="footer">
                        <h3 class="footer-title">Information</h3>
                        <ul class="footer-links">
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Contact Us</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Orders and Returns</a></li>
                            <li><a href="#">Terms & Conditions</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-3 col-xs-6">
                    <div class="footer">
                        <h3 class="footer-title">Service</h3>
                        <ul class="footer-links">
                            <li><a href="#">My Account</a></li>
                            <li><a href="#">View Cart</a></li>
                            <li><a href="#">Wishlist</a></li>
                            <li><a href="#">Track My Order</a></li>
                            <li><a href="#">Help</a></li>
                        </ul>
                    </div>
                </div>
            </div> -->
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /top footer -->

    <!-- bottom footer -->
    <div id="bottom-footer" class="section">
        <div class="container" id="hideFooterFormobile">
            <!-- row -->
            <div class="row">
                <div class="col-md-12 text-center">
                    <ul class="footer-payments p-0">
                        <!-- <li><a href="#"><i class="fa fa-cc-visa"></i></a></li>
                        <li><a href="#"><i class="fa fa-credit-card"></i></a></li>
                        <li><a href="#"><i class="fa fa-cc-paypal"></i></a></li>
                        <li><a href="#"><i class="fa fa-cc-mastercard"></i></a></li>
                        <li><a href="#"><i class="fa fa-cc-discover"></i></a></li>
                        <li><a href="#"><i class="fa fa-cc-amex"></i></a></li> -->
                    </ul>
                    <span class="copyright">
                        <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                        Copyright &copy;<script>
                            document.write(new Date().getFullYear());
                        </script> All rights reserved | TantucoCTC
                        <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                    </span>
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->

        <div class="container" id="showForMobile">
            <div class="row">
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
                            <a href="{{ route('b2b.delivery.index') }}" class="{{ Route::is('b2b.delivery.index') ? 'active-icon' : '' }}">
                                <i class="fa-solid fa-truck"></i>
                                <span>Delivery</span>
                                <!-- <div class="qty">2</div> -->
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

                        <div>
                            <a href="{{ route('b2b.purchase-requests.index') }}" 
                            class="{{ Route::is('b2b.purchase-requests.index') ? 'active-icon' : '' }}">
                                <i class="fa-solid fa-box"></i>
                                <span>PR</span>
                            </a>
                        </div>

                        <!-- Menu Toogle -->
                        @if (Route::is('home') || Route::is('welcome') )
                        <div class="menu-toggle">
                            <a href="#">
                                <i class="fa fa-bars"></i>
                                <span>Menu</span>
                            </a>
                        </div>
                        @endif
                        <!-- /Menu Toogle -->
                    </div>
                </div>
                @endauth
            </div>
        </div>

    </div>
    <!-- /bottom footer -->
</footer>
<!-- /FOOTER -->