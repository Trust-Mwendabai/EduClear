<?php
// Home page view
$title = APP_NAME . " - Student Clearance System";
?>

<style>
.hero {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('/assets/images/pattern.svg') repeat;
    opacity: 0.1;
}

.feature-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.animate-on-scroll {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

.animate-on-scroll.visible {
    opacity: 1;
    transform: translateY(0);
}

.stats-item {
    text-align: center;
    padding: 2rem;
    border-radius: 10px;
    background: #f8f9fa;
    margin-bottom: 1rem;
}

.stats-item .number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #4e73df;
    margin-bottom: 0.5rem;
}

.cta {
    background: #f8f9fa;
    padding: 4rem 0;
    border-radius: 15px;
}

@media (max-width: 768px) {
    .hero h1 {
        font-size: 2.5rem;
    }
}
</style>

<div class="hero bg-primary text-white py-5 mb-5">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-md-6 animate-on-scroll">
                <h1 class="display-4 mb-3 fw-bold">Welcome to <?= APP_NAME ?></h1>
                <p class="lead mb-4">Experience a seamless student clearance process with our modern and efficient platform.</p>
                <div class="d-grid gap-2 d-md-flex">
                    <a href="/register" class="btn btn-light btn-lg px-4 fw-bold">Get Started</a>
                    <a href="/login" class="btn btn-outline-light btn-lg px-4">Login</a>
                </div>
            </div>
            <div class="col-md-6 text-center animate-on-scroll">
                <img src="/assets/images/hero-image.svg" alt="Student Clearance" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<div class="container">
    <section class="stats mb-5 animate-on-scroll">
        <div class="row">
            <div class="col-md-3">
                <div class="stats-item">
                    <div class="number" data-count="5000">0</div>
                    <div>Students Cleared</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-item">
                    <div class="number" data-count="98">0</div>
                    <div>Success Rate</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-item">
                    <div class="number" data-count="24">0</div>
                    <div>Hour Support</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-item">
                    <div class="number" data-count="100">0</div>
                    <div>Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    <section class="features mb-5">
        <h2 class="text-center mb-4 animate-on-scroll">How It Works</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="card h-100 feature-card animate-on-scroll">
                    <div class="card-body text-center">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Register</h5>
                        <p class="card-text">Create your account with your student credentials</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 feature-card animate-on-scroll">
                    <div class="card-body text-center">
                        <i class="fas fa-money-bill fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Make Payment</h5>
                        <p class="card-text">Clear any outstanding payments securely</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 feature-card animate-on-scroll">
                    <div class="card-body text-center">
                        <i class="fas fa-tasks fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Track Progress</h5>
                        <p class="card-text">Monitor your clearance status in real-time</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 feature-card animate-on-scroll">
                    <div class="card-body text-center">
                        <i class="fas fa-certificate fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Get Cleared</h5>
                        <p class="card-text">Download your clearance certificate instantly</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="benefits mb-5">
        <div class="row align-items-center">
            <div class="col-md-6 animate-on-scroll">
                <h2 class="mb-4">Why Choose <?= APP_NAME ?>?</h2>
                <div class="list-group">
                    <div class="list-group-item border-0 mb-3 shadow-sm">
                        <h5 class="mb-1"><i class="fas fa-check-circle text-success me-2"></i> Easy to Use</h5>
                        <p class="mb-0">Simple and intuitive interface for a smooth experience</p>
                    </div>
                    <div class="list-group-item border-0 mb-3 shadow-sm">
                        <h5 class="mb-1"><i class="fas fa-shield-alt text-primary me-2"></i> Secure</h5>
                        <p class="mb-0">Your data and payments are protected with industry-standard security</p>
                    </div>
                    <div class="list-group-item border-0 mb-3 shadow-sm">
                        <h5 class="mb-1"><i class="fas fa-bolt text-warning me-2"></i> Fast</h5>
                        <p class="mb-0">Quick processing of your clearance requests</p>
                    </div>
                    <div class="list-group-item border-0 mb-3 shadow-sm">
                        <h5 class="mb-1"><i class="fas fa-headset text-info me-2"></i> 24/7 Support</h5>
                        <p class="mb-0">Get help whenever you need it</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 animate-on-scroll">
                <img src="/assets/images/benefits.svg" alt="Benefits" class="img-fluid">
            </div>
        </div>
    </section>

    <section class="testimonials mb-5">
        <h2 class="text-center mb-4 animate-on-scroll">What Students Say</h2>
        <div class="row">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-md-4 animate-on-scroll">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="fas fa-star text-warning"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="card-text"><?= htmlspecialchars($testimonial['comment']) ?></p>
                            <div class="d-flex align-items-center">
                                <img src="<?= $testimonial['avatar'] ?>" alt="<?= htmlspecialchars($testimonial['name']) ?>" 
                                     class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($testimonial['name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($testimonial['program']) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="cta text-center mb-5 animate-on-scroll">
        <div class="py-5 px-3">
            <h2 class="mb-4 fw-bold">Ready to Get Started?</h2>
            <p class="lead mb-4">Join thousands of students who have successfully completed their clearance with <?= APP_NAME ?></p>
            <a href="/register" class="btn btn-primary btn-lg px-5 py-3 fw-bold">Create Account Now</a>
        </div>
    </section>
</div>

<?php $this->push('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-on-scroll').forEach(element => {
        observer.observe(element);
    });

    // Animate statistics numbers
    function animateNumber(element, target) {
        let current = 0;
        const duration = 2000;
        const step = (target * 16) / duration;

        function update() {
            current = Math.min(current + step, target);
            element.textContent = Math.round(current);
            if (current < target) {
                requestAnimationFrame(update);
            }
        }

        update();
    }

    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const numberElement = entry.target.querySelector('.number');
                const target = parseInt(numberElement.dataset.count);
                animateNumber(numberElement, target);
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stats-item').forEach(item => {
        statsObserver.observe(item);
    });
});
</script>
<?php $this->pop('scripts') ?>
