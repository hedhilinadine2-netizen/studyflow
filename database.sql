CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    subject VARCHAR(100) DEFAULT 'General',
    task_type VARCHAR(50) DEFAULT 'Task',
    due_date DATE NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'done', 'overdue') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exam_name VARCHAR(255) NOT NULL,
    exam_type VARCHAR(100) DEFAULT 'Exam',
    subject VARCHAR(255) NOT NULL,
    mode VARCHAR(100) DEFAULT 'In Person',
    seat VARCHAR(50),
    room VARCHAR(100),
    exam_date DATE NOT NULL,
    exam_time TIME,
    duration INT,
    status VARCHAR(50) DEFAULT 'current',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);