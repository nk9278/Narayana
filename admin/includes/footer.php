    </main>
</div> <!-- End Main Content Area Wrapper -->

<!-- Sidebar Toggle Script -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');

        if (openBtn && sidebar) {
            openBtn.addEventListener('click', () => {
                sidebar.classList.remove('-translate-x-full');
            });
        }

        if (closeBtn && sidebar) {
            closeBtn.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
            });
        }
    });
</script>
</body>
</html>