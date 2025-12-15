</div> <!-- End .main-content -->

    <footer>
        <div class="container">
            <div class="row">
                <!-- Col 1 -->
                <div class="col-md-4">
                    <h5><i class="fas fa-dumbbell mr-2" style="color: var(--primary-green);"></i>WheyStore</h5>
                    <p>Unleash your potential with authentic supplements. We fuel your ambition with 100% genuine products.</p>
                    <p><i class="fas fa-map-marker-alt mr-2" style="color: var(--primary-green);"></i>1 Phan Tay Nhac Street, Hanoi</p>
                </div>

                <!-- Col 2 -->
                <div class="col-md-3">
                    <h5>Customer Support</h5>
                    <ul>
                        <li><a href="#">Shopping Guide</a></li>
                        <li><a href="#">Return Policy</a></li>
                        <li><a href="#">Payment Methods</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Col 3 -->
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <ul>
                        <li><i class="fas fa-phone-alt mr-2" style="color: var(--primary-green);"></i>Hotline: 1900 1234</li>
                        <li><i class="fas fa-envelope mr-2" style="color: var(--primary-green);"></i>support@wheystore.vn</li>
                        <li><i class="fas fa-clock mr-2" style="color: var(--primary-green);"></i>8:00 - 22:00 (Daily)</li>
                    </ul>
                </div>

                <!-- Col 4 -->
                <div class="col-md-2">
                    <h5>Follow Us</h5>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-square"></i></a>
                        <a href="#"><i class="fab fa-instagram-square"></i></a>
                        <a href="#"><i class="fab fa-youtube-square"></i></a>
                    </div>
                    <div class="mt-3">
                        <a href="#" class="btn btn-outline-light btn-sm" style="border-radius: 0; border-color: #555;">Newsletter</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom text-center">
                &copy; <?php echo date("Y"); ?> WheyStore. Power Your Life.
            </div>
        </div>
    </footer>

    <!-- Scripts JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Script Ajax Search -->
    <script>
        $(document).ready(function(){
            $('#search_input').keyup(function(){
                var query = $(this).val();
                if(query != '') {
                    $.ajax({
                        url: "search_ajax.php",
                        method: "POST",
                        data: {keyword: query},
                        success: function(data){
                            $('#search_results').fadeIn();
                            $('#search_results').html(data);
                        }
                    });
                } else {
                    $('#search_results').fadeOut();
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-container').length) {
                    $('#search_results').fadeOut();
                }
            });
        });
    </script>
</body>
</html>