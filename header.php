<header>
    <div id="logo">
    </div>
    <nav>
        <ul>
            <li>Films</li>
            <li>Catégories</li>
        </ul>
    </nav>
    <div id="log_section">
        <ul>
            <?php
                if(isset($_SESSION['is_logged'])){
                    echo "
                        <li><a href='#'>Profil</a></li>
                        <li><a href=''>Déconnexion</a></li>
                    ";
                }
            ?>
        </ul>
    </div>
</header>