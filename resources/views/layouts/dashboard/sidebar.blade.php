<!-- partial:partials/_sidebar.html -->
<nav class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand">
            Tantuco<span>CTC</span>
        </a>
        <div class="sidebar-toggler">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav" id="sidebarNav">
            <li class="nav-item nav-category">Main</li>
            <li class="nav-item {{ Route::is('home') ? 'active' : '' }}">
                <a href="{{ route('home') }}" class="nav-link">
                    <i class="link-icon" data-lucide="box"></i>
                    <span class="link-title">Dashboard</span>
                </a>
            </li>
            <li class="nav-item nav-category">Management</li>
            <li class="nav-item {{ Route::is('product-management.*') ? 'active' : '' }}">
                <a href="{{ route('product-management.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="message-square"></i>
                    <span class="link-title">Product Management</span>
                </a>
            </li>
            <li class="nav-item {{ Route::is('user-management.*') ? 'active' : '' }}">
                <a href="{{ route('user-management.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="users"></i>
                    <span class="link-title">User Management</span>
                </a>
            </li>
            <li class="nav-item nav-category">Account Creation</li>
            <li class="nav-item {{ Route::is('b2b-creation.*') ? 'active' : '' }}">
                <a href="{{ route('b2b-creation.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="message-square"></i>
                    <span class="link-title">B2B</span>
                </a>
            </li>
           <li class="nav-item {{ Route::is('delivery-rider-creation.*') ? 'active' : '' }}">
                <a href="{{ route('delivery-rider-creation.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="message-square"></i>
                    <span class="link-title">Delivery Rider</span>
                </a>
            </li>
            <li class="nav-item {{ Route::is('account-sales-creation.*') ? 'active' : '' }}">
                <a href="{{ route('account-sales-creation.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="message-square"></i>
                    <span class="link-title">Account Sales Officer</span>
                </a>
            </li>
            <li class="nav-item nav-category">Reports</li>
            <li class="nav-item {{ Route::is('user.report') ? 'active' : '' }}">
                <a href="{{ route('user.report') }}" class="nav-link">
                    <i class="link-icon" data-lucide="message-square"></i>
                    <span class="link-title">User Reports</span>
                </a>
            </li>
            <li class="nav-item {{ Route::is('delivery.report') ? 'active' : '' }}">
                <a href="{{ route('delivery.report') }}" class="nav-link">
                    <i class="link-icon" data-lucide="message-square"></i>
                    <span class="link-title">Delivery Reports</span>
                </a>
            </li>
            <li class="nav-item {{ Route::is('inventory.report') ? 'active' : '' }}">
                <a href="{{ route('inventory.report') }}" class="nav-link">
                    <i class="link-icon" data-lucide="message-square"></i>
                    <span class="link-title">Inventory Reports</span>
                </a>
            </li>
            <li class="nav-item nav-category">Tracking</li>
            <li class="nav-item">
                <a href="pages/apps/chat.html" class="nav-link">
                    <i class="link-icon" data-lucide="message-square"></i>
                    <span class="link-title">Track Deliveries</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pages/apps/chat.html" class="nav-link">
                    <i class="link-icon" data-lucide="message-square"></i>
                    <span class="link-title">Assign Delivery Driver</span>
                </a>
            </li>


            <!-- <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#emails" role="button" aria-expanded="false" aria-controls="emails">
                    <i class="link-icon" data-lucide="mail"></i>
                    <span class="link-title">Email</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse" data-bs-parent="#sidebarNav" id="emails">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="pages/email/inbox.html" class="nav-link">Inbox</a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/email/read.html" class="nav-link">Read</a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/email/compose.html" class="nav-link">Compose</a>
                        </li>
                    </ul>
                </div>
            </li> -->

        </ul>
    </div>
</nav>
<!-- partial -->