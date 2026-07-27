<?php
// Grab a success/error message passed back after submission, if any
session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$lastStudent = $_SESSION['last_student'] ?? null;
unset($_SESSION['last_student']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Registration</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="shell">

    <!-- ============== LEFT: Brand / Ledger panel ============== -->
    <aside class="brand-panel">
        <div>
            <div class="brand-mark">
                <div class="seal">SR</div>
                <span class="name">Student Registration</span>
            </div>

            <div class="brand-copy">
                <div class="eyebrow">Academic Year 2026&ndash;27</div>
                <h1>Enrolment starts with one clean form.</h1>
                <p>Fill in your details on the right — every field checks itself as you go, so there are no surprises when you hit submit.</p>
            </div>

            <div class="ledger">
                <div class="ledger-head">
                    <span>Form completion</span>
                    <span class="ledger-count" id="ledgerCount">0%</span>
                </div>
                <div class="ledger-track">
                    <div class="ledger-fill" id="ledgerFill"></div>
                </div>
                <ul class="ledger-items">
                    <li data-key="li-identity"><span class="dot"></span> Identity &amp; basic details</li>
                    <li data-key="li-contact"><span class="dot"></span> Contact information</li>
                    <li data-key="li-academic"><span class="dot"></span> Academic &amp; address details</li>
                    <li data-key="li-security"><span class="dot"></span> Account security</li>
                </ul>
            </div>
        </div>

        <div class="brand-foot">
            Having trouble? Reach the admissions desk at <br> admissions@example.edu
        </div>
    </aside>

    <!-- ============== RIGHT: Form panel ============== -->
    <main class="form-panel">
        <div class="form-wrap">

            <?php if ($flash && $flash['type'] === 'success' && $lastStudent): ?>
                <!-- ---------- Success / receipt screen ---------- -->
                <div class="success-screen">
                    <div class="stamp">
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none">
                            <path d="M20 6L9 17l-5-5" stroke="#2f8f5b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h2>You're registered.</h2>
                    <p>Your details have been recorded. Keep the reference below for your records.</p>

                    <div class="receipt">
                        <div><span>Reference No.</span><span>STU-<?= str_pad($lastStudent['id'], 5, '0', STR_PAD_LEFT) ?></span></div>
                        <div><span>Name</span><span><?= htmlspecialchars($lastStudent['full_name']) ?></span></div>
                        <div><span>Course</span><span><?= htmlspecialchars($lastStudent['course']) ?></span></div>
                        <div><span>Email</span><span><?= htmlspecialchars($lastStudent['email']) ?></span></div>
                    </div>

                    <a href="index.php" class="btn-secondary">Register another student</a>
                </div>

            <?php else: ?>

                <div class="form-head">
                    <div class="kicker">New Admission</div>
                    <h2>Student Registration Form</h2>
                    <p>All fields are required unless marked optional. Fields validate instantly as you type.</p>
                </div>

                <?php if ($flash && $flash['type'] === 'error'): ?>
                    <div class="alert-banner error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#c1473f" stroke-width="2"/><path d="M12 8v5M12 16h.01" stroke="#c1473f" stroke-width="2" stroke-linecap="round"/></svg>
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <form id="registrationForm" action="submit_registration.php" method="POST" novalidate>

                    <div class="field-row">
                        <div class="field">
                            <label for="full_name">Full name</label>
                            <div class="input-wrap">
                                <input type="text" id="full_name" name="full_name" placeholder="e.g. Charu Sharma" autocomplete="name">
                                <svg class="status-icon icon-valid" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#2f8f5b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg class="status-icon icon-invalid" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#c1473f" stroke-width="2"/><path d="M12 8v5M12 16h.01" stroke="#c1473f" stroke-width="2" stroke-linecap="round"/></svg>
                            </div>
                            <div class="hint"></div>
                        </div>

                        <div class="field">
                            <label for="dob">Date of birth</label>
                            <div class="input-wrap">
                                <input type="date" id="dob" name="dob">
                            </div>
                            <div class="hint"></div>
                        </div>
                    </div>

                    <div class="field">
                        <label>Gender</label>
                        <div class="pill-group">
                            <input type="radio" id="gender_male" name="gender" value="Male">
                            <label for="gender_male">Male</label>
                            <input type="radio" id="gender_female" name="gender" value="Female">
                            <label for="gender_female">Female</label>
                            <input type="radio" id="gender_other" name="gender" value="Other">
                            <label for="gender_other">Other</label>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="email">Email address</label>
                            <div class="input-wrap">
                                <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email">
                                <svg class="status-icon icon-valid" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#2f8f5b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg class="status-icon icon-invalid" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#c1473f" stroke-width="2"/><path d="M12 8v5M12 16h.01" stroke="#c1473f" stroke-width="2" stroke-linecap="round"/></svg>
                            </div>
                            <div class="hint"></div>
                        </div>

                        <div class="field">
                            <label for="phone">Mobile number</label>
                            <div class="input-wrap">
                                <input type="tel" id="phone" name="phone" placeholder="10-digit number" autocomplete="tel">
                                <svg class="status-icon icon-valid" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#2f8f5b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg class="status-icon icon-invalid" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#c1473f" stroke-width="2"/><path d="M12 8v5M12 16h.01" stroke="#c1473f" stroke-width="2" stroke-linecap="round"/></svg>
                            </div>
                            <div class="hint"></div>
                        </div>
                    </div>

                    <div class="field">
                        <label for="course">Course applying for</label>
                        <select id="course" name="course">
                            <option value="">Select a course</option>
                            <option value="B.Tech - Computer Science">B.Tech — Computer Science</option>
                            <option value="B.Tech - AI & Data Science">B.Tech — AI &amp; Data Science</option>
                            <option value="B.Tech - Electronics">B.Tech — Electronics</option>
                            <option value="B.Tech - Mechanical">B.Tech — Mechanical</option>
                            <option value="BCA">BCA</option>
                            <option value="MBA">MBA</option>
                        </select>
                        <div class="hint"></div>
                    </div>

                    <div class="field">
                        <label for="address">Residential address</label>
                        <textarea id="address" name="address" placeholder="House no., street, city, state, PIN"></textarea>
                        <div class="hint"></div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="password">Create password</label>
                            <div class="input-wrap">
                                <input type="password" id="password" name="password" placeholder="Min. 8 characters" autocomplete="new-password">
                            </div>
                            <div class="strength-meter">
                                <i></i><i></i><i></i><i></i>
                            </div>
                            <div class="hint"></div>
                        </div>

                        <div class="field">
                            <label for="confirm_password">Confirm password</label>
                            <div class="input-wrap">
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" autocomplete="new-password">
                                <svg class="status-icon icon-valid" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#2f8f5b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg class="status-icon icon-invalid" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#c1473f" stroke-width="2"/><path d="M12 8v5M12 16h.01" stroke="#c1473f" stroke-width="2" stroke-linecap="round"/></svg>
                            </div>
                            <div class="hint"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn" disabled>
                        <span class="spinner"></span>
                        <span class="btn-text">Submit Registration</span>
                    </button>

                    <div class="form-foot">
                        Are you an administrator? <a href="admin/login.php">Go to admin panel</a>
                    </div>
                </form>

            <?php endif; ?>

        </div>
    </main>
</div>

<script src="assets/js/validation.js"></script>
</body>
</html>
