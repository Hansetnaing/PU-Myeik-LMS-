-- LearnHub V2 schema. Import this file in phpMyAdmin before running the app.
CREATE DATABASE IF NOT EXISTS learnhub_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE learnhub_v2;

CREATE TABLE teacher (
  teacher_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  dept_name VARCHAR(100) NOT NULL
);

CREATE TABLE student (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  year VARCHAR(255) NOT NULL,
  major VARCHAR(100) NOT NULL
);

CREATE TABLE course (
  course_id INT AUTO_INCREMENT PRIMARY KEY,
  course_name VARCHAR(100) NOT NULL,
  subject VARCHAR(100) NOT NULL,
  year VARCHAR(100) NOT NULL,
  section VARCHAR(100) NOT NULL,
  teacher_id INT NULL,
  CONSTRAINT fk_course_teacher FOREIGN KEY (teacher_id) REFERENCES teacher(teacher_id)
    ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE student_course (
  student_id INT NOT NULL,
  course_id INT NOT NULL,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (student_id, course_id),
  CONSTRAINT fk_student_course_student FOREIGN KEY (student_id) REFERENCES student(student_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_student_course_course FOREIGN KEY (course_id) REFERENCES course(course_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE assignment (
  assignment_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  file VARCHAR(255),
  due_date DATE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  teacher_id INT NULL,
  course_id INT NOT NULL,
  CONSTRAINT fk_assignment_teacher FOREIGN KEY (teacher_id) REFERENCES teacher(teacher_id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_assignment_course FOREIGN KEY (course_id) REFERENCES course(course_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE lecture (
  lecture_id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  file VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  course_id INT NOT NULL,
  CONSTRAINT fk_lecture_teacher FOREIGN KEY (teacher_id) REFERENCES teacher(teacher_id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_lecture_course FOREIGN KEY (course_id) REFERENCES course(course_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE submit_assignment (
  submission_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  assignment_id INT NOT NULL,
  file VARCHAR(255),
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_submission (student_id, assignment_id),
  CONSTRAINT fk_submission_student FOREIGN KEY (student_id) REFERENCES student(student_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_submission_assignment FOREIGN KEY (assignment_id) REFERENCES assignment(assignment_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX idx_course_teacher ON course(teacher_id);
CREATE INDEX idx_student_course_course ON student_course(course_id);
CREATE INDEX idx_assignment_course ON assignment(course_id);
CREATE INDEX idx_lecture_course ON lecture(course_id);
CREATE INDEX idx_submission_student ON submit_assignment(student_id);
CREATE INDEX idx_submission_assignment ON submit_assignment(assignment_id);
