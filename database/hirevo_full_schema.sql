-- ============================================
-- HIREVO - Full Database Schema (MySQL)
-- Run this file to create all tables at once
-- ============================================
-- Usage: mysql -u root -p hirevo < database/hirevo_full_schema.sql
-- Or create database first: CREATE DATABASE hirevo; USE hirevo; then run this.
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

-- Users
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS rewards;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS bids;
DROP TABLE IF EXISTS leads;
DROP TABLE IF EXISTS referral_requests;
DROP TABLE IF EXISTS skill_analysis;
DROP TABLE IF EXISTS resumes;
DROP TABLE IF EXISTS job_required_skills;
DROP TABLE IF EXISTS job_roles;
DROP TABLE IF EXISTS edtech_profiles;
DROP TABLE IF EXISTS referrer_profiles;
DROP TABLE IF EXISTS candidate_profiles;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(255) NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('candidate','referrer','edtech','admin') DEFAULT 'candidate',
    status ENUM('active','blocked','pending') DEFAULT 'active',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
);

CREATE TABLE candidate_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    headline VARCHAR(255) NULL,
    education TEXT NULL,
    experience_years INT NULL,
    skills TEXT NULL,
    location VARCHAR(255) NULL,
    expected_salary VARCHAR(255) NULL,
    is_premium TINYINT(1) DEFAULT 0,
    premium_expires_at TIMESTAMP NULL,
    referral_requests_used INT DEFAULT 0,
    referral_requests_limit INT DEFAULT 3,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE referrer_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    company_email VARCHAR(255) NOT NULL UNIQUE,
    company_email_verified TINYINT(1) DEFAULT 0,
    designation VARCHAR(255) NULL,
    department VARCHAR(255) NULL,
    is_approved TINYINT(1) DEFAULT 0,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE edtech_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NULL,
    phone VARCHAR(255) NULL,
    courses_offered TEXT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    verified_at TIMESTAMP NULL,
    balance DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE job_roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NULL UNIQUE,
    description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE job_required_skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_role_id BIGINT UNSIGNED NOT NULL,
    skill_name VARCHAR(255) NOT NULL,
    priority INT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX jrs_role_skill (job_role_id, skill_name),
    FOREIGN KEY (job_role_id) REFERENCES job_roles(id) ON DELETE CASCADE
);

CREATE TABLE resumes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NULL,
    mime_type VARCHAR(255) NULL,
    ai_score INT NULL,
    ai_score_explanation TEXT NULL,
    ai_summary TEXT NULL,
    extracted_skills JSON NULL,
    is_primary TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE skill_analysis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    job_role_id BIGINT UNSIGNED NOT NULL,
    resume_id BIGINT UNSIGNED NULL,
    match_percentage INT NOT NULL,
    matched_skills JSON NULL,
    missing_skills JSON NULL,
    learning_roadmap TEXT NULL,
    skill_gap_explanation TEXT NULL,
    intent_score INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX sa_user_role (user_id, job_role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_role_id) REFERENCES job_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE SET NULL
);

CREATE TABLE referral_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id BIGINT UNSIGNED NOT NULL,
    referrer_id BIGINT UNSIGNED NOT NULL,
    job_role_id BIGINT UNSIGNED NULL,
    message TEXT NULL,
    status ENUM('pending','accepted','rejected','hired','reward_paid') DEFAULT 'pending',
    responded_at TIMESTAMP NULL,
    referrer_notes TEXT NULL,
    hire_verified TINYINT(1) DEFAULT 0,
    hire_verified_at TIMESTAMP NULL,
    verified_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX rr_candidate_referrer (candidate_id, referrer_id),
    INDEX rr_status (status),
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_role_id) REFERENCES job_roles(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id BIGINT UNSIGNED NOT NULL,
    skill_analysis_id BIGINT UNSIGNED NOT NULL,
    job_role_id BIGINT UNSIGNED NOT NULL,
    match_percentage INT NOT NULL,
    missing_skills JSON NULL,
    intent_score INT NULL,
    lead_summary TEXT NULL,
    status ENUM('available','bidding','sold','contact_unlocked') DEFAULT 'available',
    bidding_ends_at TIMESTAMP NULL,
    minimum_bid DECIMAL(10,2) NULL,
    won_by_edtech_id BIGINT UNSIGNED NULL,
    sold_amount DECIMAL(10,2) NULL,
    sold_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX leads_status (status),
    INDEX leads_bidding_ends (bidding_ends_at),
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_analysis_id) REFERENCES skill_analysis(id) ON DELETE CASCADE,
    FOREIGN KEY (job_role_id) REFERENCES job_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (won_by_edtech_id) REFERENCES edtech_profiles(id) ON DELETE SET NULL
);

CREATE TABLE bids (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    edtech_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('placed','won','lost','cancelled') DEFAULT 'placed',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX bids_lead_amount (lead_id, amount),
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (edtech_id) REFERENCES edtech_profiles(id) ON DELETE CASCADE
);

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    payment_gateway VARCHAR(255) NULL,
    payment_reference VARCHAR(255) NULL,
    status VARCHAR(255) DEFAULT 'pending',
    meta JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX payments_user_type (user_id, type),
    INDEX payments_reference (payment_reference),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE rewards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    referrer_id BIGINT UNSIGNED NOT NULL,
    referral_request_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','paid','cancelled') DEFAULT 'pending',
    payment_id BIGINT UNSIGNED NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referral_request_id) REFERENCES referral_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL
);

CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX notifications_notifiable (notifiable_type, notifiable_id)
);

CREATE TABLE admin_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id BIGINT UNSIGNED NULL,
    action VARCHAR(255) NOT NULL,
    target_type VARCHAR(255) NULL,
    target_id BIGINT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX admin_logs_target (target_type, target_id),
    INDEX admin_logs_created (created_at),
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
);

SET FOREIGN_KEY_CHECKS = 1;
