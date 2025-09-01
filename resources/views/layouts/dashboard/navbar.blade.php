<!-- partial:partials/_navbar.html -->
<nav class="navbar" style="background-color:#FC6319;">
    <div class="navbar-content">

        <div class="logo-mini-wrapper">
            <img src="{{ asset($companySettings->company_logo  ?? 'assets/dashboard/images/noimage.png'  ) }}" class="logo-mini logo-mini-light" alt="logo">
            <img src="{{ asset($companySettings->company_logo  ?? 'assets/dashboard/images/noimage.png'  ) }}" class="logo-mini logo-mini-dark" alt="logo">
        </div>

        <form class="search-form">
            <div class="input-group d-none">
                <div class="input-group-text">
                    <i data-lucide="search"></i>
                </div>
                <input type="text" class="form-control" id="navbarForm" placeholder="Search here...">
            </div>
        </form>

        <ul class="navbar-nav">
            <li class="theme-switcher-wrapper nav-item">
                <input type="checkbox" value="" id="theme-switcher">
                <label for="theme-switcher">
                    <div class="box">
                        <div class="ball"></div>
                        <div class="icons">
                            <i data-lucide="sun"></i>
                            <i data-lucide="moon"></i>
                        </div>
                    </div>
                </label>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-lucide="mail"></i>
                    <div class="indicator d-none" id="messageIndicator">
                        <div class="circle"></div>
                    </div>
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="messageDropdown">
                    <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                        <p id="messageCount">0 New Messages</p>
                        <a href="javascript:;" class="text-secondary mx-2">Clear all</a>
                    </div>
                    <div class="p-1" id="recentMessagesList">
                        <div class="dropdown-item py-2 text-center text-muted">No new messages</div>
                    </div>
                    <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
                        <a href="{{ route('chat.index') }}">Go to Messages</a>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-lucide="bell"></i>
                    <div class="indicator" id="notificationIndicator" style="display:none;">
                        <div class="circle"></div>
                    </div>
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown">
                    <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                        <p id="notificationCount">0 New Notifications</p>
                        <a href="javascript:;" class="text-secondary mx-2" onclick="markAllNotificationsRead()">Clear all</a>
                    </div>
                    <div class="p-1" id="notificationItems">
                        <div class="text-center py-2 text-muted">Loading...</div>
                    </div>
                    <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
                        <a href="{{ route('notification.index') }}">View all</a>
                    </div>
                </div>
            </li>
            <!-- <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-lucide="bell"></i>
                    <div class="indicator">
                        <div class="circle"></div>
                    </div>
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown">
                    <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                        <p>6 New Notifications</p>
                        <a href="javascript:;" class="text-secondary">Clear all</a>
                    </div>
                    <div class="p-1">
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                                <i class="icon-sm text-white" data-lucide="gift"></i>
                            </div>
                            <div class="flex-grow-1 me-2">
                                <p>New Order Recieved</p>
                                <p class="fs-12px text-secondary">30 min ago</p>
                            </div>
                        </a>
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                                <i class="icon-sm text-white" data-lucide="alert-circle"></i>
                            </div>
                            <div class="flex-grow-1 me-2">
                                <p>Server Limit Reached!</p>
                                <p class="fs-12px text-secondary">1 hrs ago</p>
                            </div>
                        </a>
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                                <img class="w-30px h-30px rounded-circle" src="{{ asset('assets/dashboard/images/faces/face6.jpg') }}" alt="userr">
                            </div>
                            <div class="flex-grow-1 me-2">
                                <p>New customer registered</p>
                                <p class="fs-12px text-secondary">2 sec ago</p>
                            </div>
                        </a>
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                                <i class="icon-sm text-white" data-lucide="layers"></i>
                            </div>
                            <div class="flex-grow-1 me-2">
                                <p>Apps are ready for update</p>
                                <p class="fs-12px text-secondary">5 hrs ago</p>
                            </div>
                        </a>
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                                <i class="icon-sm text-white" data-lucide="download"></i>
                            </div>
                            <div class="flex-grow-1 me-2">
                                <p>Download completed</p>
                                <p class="fs-12px text-secondary">6 hrs ago</p>
                            </div>
                        </a>
                    </div>
                    <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
                        <a href="javascript:;">View all</a>
                    </div>
                </div>
            </li> -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="w-30px h-30px ms-1 rounded-circle profile-image" src="#" alt="profile">
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                        <div class="mb-3">
                            <img class="w-80px h-80px rounded-circle profile-image" src="#" alt="">
                        </div>
                        <div class="text-center">
                            <p class="fs-16px fw-bolder profile-name"></p>
                            <p class="fs-12px text-secondary profile-email"></p>
                        </div>
                    </div>
                    <ul class="list-unstyled p-1">
                        <li>
                            <a href="{{ route('profile.settings') }}" class="dropdown-item py-2 text-body ms-0">
                                <i class="me-2 icon-md" data-lucide="user"></i>
                                <span>Profile</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();" class="dropdown-item py-2 text-body ms-0">
                                <i class="me-2 icon-md" data-lucide="log-out"></i>
                                <span>Log Out</span>
                            </a>
                            <form id="logout-form-admin" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>

        <a href="#" class="sidebar-toggler">
            <i data-lucide="menu"></i>
        </a>

    </div>
</nav>
<!-- partial -->