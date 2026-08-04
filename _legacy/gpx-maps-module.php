</main>
<footer>  #MagicSoulTravel #Wanderlust #TravelMore #HiddenGems #AdventureAwaits #BucketListTravel #ExploreTheWorld #TravelVibes #NatureLover #Globetrotter #SaintLucia #Barbados #Martinique #CaribbeanTravels #CaribbeanAirlines #BritishAirways #LOTPolishAirlines #ExpressDesIles #FerryRide #CruiseShip #Concert #LiveBand #MilitaryOrchestra #RelaxingVideos #NatureSounds #AmbientVideos #MardiGras #CarnivalVibes #Sunset #Timelapse #ParadiseCity #ParadiseIsland<br><br>
    &copy; <?= date('Y') ?> magicsoultravel.com<br><br>

   <div class="bottom-login-status">
        <?php if ($isLoggedIn): ?>
            <?php if (is_admin()): // Assuming is_admin() is defined in auth.php and requires session ?>
                <nav class="admin-nav">
                    <?php
                    // Dynamically generate the correct path for the Admin Panel link
                    $adminPanelLink = dirname($_SERVER['SCRIPT_NAME']) . '../admin/panel.php';
                    ?>
                    <a href="<?= htmlspecialchars($adminPanelLink) ?>">Admin Panel</a>
                </nav>
            <?php endif; ?>

            <div class="user-status">
                Status: Logged in as <?= htmlspecialchars($email) ?> |
                <a href="../auth/logout.php">Logout</a>
            </div>
        <?php else: ?>
            
 <?php
            
//Dynamically generate the correct form action path
            $formActionPath = dirname($_SERVER['SCRIPT_NAME']) . '../auth/login.php';
            ?>
            <form class="login-form" method="post" action="<?= htmlspecialchars($formActionPath) ?>" autocomplete="off">
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit">Login</button>
            </form>

            <div class="user-status">
                Status: Logged out
            </div>
        <?php endif; ?>
    </div>
</footer>

</body>
    <?php include __DIR__ . '/../inc/google-scripts.php'; ?>
</html>