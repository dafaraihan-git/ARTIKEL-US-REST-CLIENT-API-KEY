<?php
include("config/config.php");

// Memuat Composer Autoload
require_once __DIR__ . "/vendor/autoload.php";

// Membaca file .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/");
$dotenv->load();

$api_key = $_ENV["NEWSAPIKEY"] ?? "";
$hasil = null;

if ($api_key !== "") {
    $url = "https://newsapi.org/v2/top-headlines?country=us&apiKey=" . urlencode($api_key);
    $data = http_request_get($url);
    $hasil = is_string($data) ? json_decode($data, true) : null;
}

$articles = is_array($hasil) ? ($hasil['articles'] ?? []) : [];
$api_error = $api_key === "" || !is_array($hasil) || ($hasil['status'] ?? null) === 'error';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>NewsToday | Berita Terkini</title>

    <!-- Bootstrap 6.0.0 Alpha 1: CSS utama -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --news-primary: #2563eb;
            --news-text: #172033;
            --news-muted: #718096;
            --news-border: #e8edf3;
            --news-bg: #f7f9fc;
        }

        body {
            font-family: "Inter", sans-serif;
            color: var(--news-text);
            background: var(--news-bg);
        }

        a { text-decoration: none; }

        .site-navbar {
            background: #fff;
            border-bottom: 1px solid var(--news-border);
        }

        .brand {
            color: var(--news-primary);
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -.7px;
        }

        .brand span { color: var(--news-text); }

        .nav-link {
            color: #64748b;
            font-size: .9rem;
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active { color: var(--news-primary); }

        .hero {
            padding: 3.5rem 0 2.8rem;
            text-align: center;
        }

        .hero-kicker {
            display: inline-block;
            padding: .45rem .85rem;
            border-radius: 50rem;
            background: #eaf1ff;
            color: var(--news-primary);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .06em;
        }

        .hero h1 {
            margin: 1rem 0 .65rem;
            font-size: clamp(1.8rem, 4vw, 2.65rem);
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: -1.2px;
        }

        .hero h1 span { color: var(--news-primary); }

        .hero p {
            max-width: 590px;
            margin: 0 auto;
            color: var(--news-muted);
            font-size: .95rem;
            line-height: 1.7;
        }

        .news-section { padding-bottom: 4rem; }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .section-heading h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 750;
            letter-spacing: -.4px;
        }

        .article-count {
            color: var(--news-muted);
            font-size: .8rem;
        }

        .news-card {
            height: 100%;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--news-border);
            border-radius: .85rem;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .news-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(23, 32, 51, .07);
        }

        .news-image,
        .image-placeholder {
            display: block;
            width: 100%;
            height: 165px;
            object-fit: cover;
            background: #e9eef5;
        }

        .image-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8290a3;
            font-size: .8rem;
        }

        .news-card-body {
            display: flex;
            flex-direction: column;
            padding: 1rem;
            min-height: 205px;
        }

        .news-label {
            align-self: flex-start;
            margin-bottom: .7rem;
            padding: .3rem .55rem;
            border-radius: .35rem;
            background: #eff4ff;
            color: var(--news-primary);
            font-size: .64rem;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .news-title {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            margin: 0 0 .55rem;
            color: var(--news-text);
            font-size: .98rem;
            font-weight: 700;
            line-height: 1.5;
        }

        .news-description {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            margin-bottom: 1rem;
            color: var(--news-muted);
            font-size: .79rem;
            line-height: 1.65;
        }

        .news-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            margin-top: auto;
            padding-top: .8rem;
            border-top: 1px solid #f0f2f6;
        }

        .news-author {
            overflow: hidden;
            color: #8995a5;
            font-size: .7rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .read-more {
            flex-shrink: 0;
            color: var(--news-primary);
            font-size: .75rem;
            font-weight: 700;
        }

        .read-more:hover { color: #1749b5; }

        .empty-state {
            padding: 2.5rem 1rem;
            border: 1px solid var(--news-border);
            border-radius: .85rem;
            background: #fff;
            text-align: center;
        }

        .empty-state p {
            margin: .5rem 0 0;
            color: var(--news-muted);
            font-size: .85rem;
        }

        .site-footer {
            padding: 1.4rem 0;
            border-top: 1px solid var(--news-border);
            background: #fff;
            color: #8995a5;
            text-align: center;
            font-size: .75rem;
        }

        @media (max-width: 575.98px) {
            .hero { padding: 2.6rem 0 2rem; }
            .hero p { font-size: .85rem; }
            .news-image, .image-placeholder { height: 190px; }
            .news-card-body { min-height: auto; }
        }
    </style>
</head>
<body>

<!-- Navbar menggunakan komponen dan utility Bootstrap -->
<nav class="navbar site-navbar">
    <div class="container py-2">
        <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
            <a class="brand" href="#">News<span>Today.</span></a>

            <div class="d-flex align-items-center gap-3 gap-md-4">
                <a class="nav-link active p-0" href="#">Home</a>
                <a class="nav-link p-0" href="#news">News</a>
                <a class="nav-link p-0" href="#about">About</a>
            </div>
        </div>
    </div>
</nav>

<header class="hero">
    <div class="container">
        <span class="hero-kicker">YOUR DAILY NEWS UPDATE</span>
        <h1>Informasi Terbaru,<br><span>Wawasan Tanpa Batas.</span></h1>
        <p>Temukan berbagai berita dan informasi terkini dari seluruh dunia.</p>
    </div>
</header>

<main class="container news-section" id="news">
    <div class="section-heading">
        <h2>Berita Terbaru</h2>
        <span class="article-count"><?php echo count($articles); ?> artikel</span>
    </div>

    <!-- Bootstrap 6: responsive breakpoint classes use sm:col-* and lg:col-* -->
    <div class="row g-4">
        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $row): ?>
                <?php
                    $title = $row['title'] ?? 'Judul tidak tersedia';
                    $author = $row['author'] ?? 'Redaksi';
                    $description = $row['description'] ?? 'Deskripsi berita tidak tersedia.';
                    $image = $row['urlToImage'] ?? '';
                    $articleUrl = $row['url'] ?? '';
                ?>

                <div class="col-12 sm:col-6 lg:col-4">
                    <article class="news-card">
                        <?php if (!empty($image)): ?>
                            <img
                                src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>"
                                class="news-image"
                                alt="Gambar berita"
                                loading="lazy"
                                onerror="this.style.display='none';"
                            >
                        <?php else: ?>
                            <div class="image-placeholder">Gambar tidak tersedia</div>
                        <?php endif; ?>

                        <div class="news-card-body">
                            <span class="news-label">LATEST NEWS</span>

                            <h3 class="news-title">
                                <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                            </h3>

                            <p class="news-description">
                                <?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <div class="news-meta">
                                <span class="news-author">
                                    Oleh <?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <?php if (!empty($articleUrl)): ?>
                                    <a
                                        class="read-more"
                                        href="<?php echo htmlspecialchars($articleUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >Baca selengkapnya &rarr;</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <h3 class="h5 fw-bold">Berita belum tersedia</h3>
                    <p>
                        <?php
                        if ($api_key === "") {
                            echo "API Key belum dikonfigurasi. Tambahkan NEWSAPIKEY ke file .env.";
                        } elseif ($api_error) {
                            echo "NewsAPI mengembalikan error. Periksa API Key dan kuota API kamu.";
                        } else {
                            echo "Tidak ada artikel yang diterima. Periksa koneksi atau respons API.";
                        }
                        ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="site-footer" id="about">
    <div class="container">
        &copy; <?php echo date("Y"); ?> <strong>NewsToday.</strong> All Rights Reserved.
    </div>
</footer>

<!-- Bootstrap 6 Alpha bundle. Halaman ini tidak memerlukan jQuery atau Popper terpisah. -->
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
