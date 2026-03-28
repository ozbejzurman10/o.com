<?php
session_start();
require_once "config/db.php";
require_once "helpers/helpers.php";


$stmt = $conn->prepare("SELECT * FROM posts ORDER BY created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);


// izbrisi post
if (isset($_POST['delete_post'])) {
    $post_id = $_POST['post_id'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    header("Location: index.php");
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Home</title>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="centered-container">
            <div class="logo">O</div>
            <div class="subtitle">Latest Posts</div>
        </div>
    </div>

    <div class="main-posts">

        <div class="posts-column">
            <div class="posts-toolbar">
                <label class="posts-toolbar-search">
                    <input type="search" id="posts-search" class="posts-search-input" placeholder="Search titles…">
                </label>
                <label class="posts-toolbar-sort">
                    <span class="posts-sort-label">Sort</span>
                    <select id="posts-sort" class="posts-sort-select">
                        <option value="newest">Newest (date)</option>
                        <option value="oldest">Oldest (date)</option>
                        <option value="likes-desc">Most likes</option>
                        <option value="likes-asc">Least likes</option>
                    </select>
                </label>
            </div>

            <div class="posts-wrapper" id="posts-wrapper">
                <?php foreach ($posts as $post): ?>
                    <?php $likes_count = getLikesCount($conn, $post['id']); ?>
                    <div class="post" data-post-id="<?php echo $post['id']; ?>"
                        data-created-at="<?php echo htmlspecialchars($post['created_at']); ?>"
                        data-likes="<?php echo $likes_count; ?>">
                        <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>

                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>

                        <div class="post-footer">


                            <a href="profile.php?id=<?php echo $post['user_id'] ?>" class="post-bottom-profile">
                                <span class="post-author">
                                    <img class="post-author-pic"
                                        src="profile_images/<?php echo htmlspecialchars(getUserPfpById($conn, $post['user_id']) ?: 'default.png'); ?>"
                                        alt="Avatar">
                                    <span><?php echo htmlspecialchars(getUsernameById($conn, $post['user_id'])); ?></span>
                                </span>
                            </a>

                            <div class="post-bottom">
                                <?php echo htmlspecialchars($post['created_at']); ?>
                            </div>


                            <div class="post-bottom-right">

                                <?php echo $likes_count; ?>

                                <?php
                                if (isset($_SESSION['user_id'])):
                                    $liked = isLiked($conn, $_SESSION['user_id'], $post['id']);
                                else:
                                    $liked = false;
                                    ?>

                                <?php endif; ?>


                                <form method="POST" action="helpers/like_handler.php" style="display:inline;">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

                                    <button type="submit" name="like_post"
                                        class="<?php echo $liked ? 'liked-btn' : 'not-liked-btn'; ?>">

                                        <?php echo "❤︎" ?>
                                    </button>
                                </form>
                            </div>

                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <script>
        (function () {
            var wrapper = document.getElementById('posts-wrapper');
            var searchInput = document.getElementById('posts-search');
            var sortSelect = document.getElementById('posts-sort');
            if (!wrapper || !searchInput || !sortSelect) return;

            function postTimestamp(el) {
                var raw = el.getAttribute('data-created-at') || '';
                var t = new Date(raw.replace(' ', 'T')).getTime();
                return isNaN(t) ? 0 : t;
            }

            function postLikes(el) {
                return parseInt(el.getAttribute('data-likes'), 10) || 0;
            }

            function postId(el) {
                return parseInt(el.getAttribute('data-post-id'), 10) || 0;
            }

            function sortPosts() {
                var posts = Array.prototype.slice.call(wrapper.querySelectorAll('.post'));
                var mode = sortSelect.value;

                posts.sort(function (a, b) {
                    var cmp = 0;
                    if (mode === 'newest') {
                        cmp = postTimestamp(b) - postTimestamp(a);
                    } else if (mode === 'oldest') {
                        cmp = postTimestamp(a) - postTimestamp(b);
                    } else if (mode === 'likes-desc') {
                        cmp = postLikes(b) - postLikes(a);
                    } else if (mode === 'likes-asc') {
                        cmp = postLikes(a) - postLikes(b);
                    }
                    if (cmp !== 0) return cmp;
                    return postId(b) - postId(a);
                });

                posts.forEach(function (p) {
                    wrapper.appendChild(p);
                });
            }

            function filterByTitle() {
                var q = (searchInput.value || '').trim().toLowerCase();
                wrapper.querySelectorAll('.post').forEach(function (el) {
                    var titleEl = el.querySelector('.post-title');
                    var title = titleEl ? titleEl.textContent.toLowerCase() : '';
                    el.style.display = !q || title.indexOf(q) !== -1 ? '' : 'none';
                });
            }

            sortSelect.addEventListener('change', function () {
                sortPosts();
                filterByTitle();
            });

            searchInput.addEventListener('input', filterByTitle);

            sortPosts();
            filterByTitle();
        })();
    </script>

</body>

</html>