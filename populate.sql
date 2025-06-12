
-- 3. students_data
INSERT INTO students_data (
    uniqueID, first_name, middle_name, last_name, student_ID, stud_dept, stud_course, 
    stud_section, complete_address, stud_gender, phone_number, stud_email, stud_password, 
    guardians_name, guardians_cpNumber, profile_picture, verification_code, verify_status, 
    online_offlineStatus, ojt_status, year_lvl, stud_hte, total_rendered_hours, 
    medical_condition, is_working_student, stud_department, sis_document, verification_status, 
    required_hours, supervisor_id, company
) VALUES
('UID001', 'Alice', 'K', 'Johnson', 'SID001', 'Institute of Technology', 'Diploma in Civil Engineering Technology (DCvET)', 'Section A', '101 Main St, City A', 'Female', '12345678011', 'alice.johnson@example.com', 'pass123', 'Guardian1', '09876543201', 'spic1.jpg', 123456, 'Not Verified', 'Offline', 'Pending', '1', 'TechCorp', 0, 'None', 'no', 'Technology', 'sis1.pdf', 'pending', 100, 1, 'TechCorp'),
('UID002', 'Bob', 'L', 'Miller', 'SID002', 'Institute of Technology', 'Diploma in Computer Engineering Technology (DCET)', 'Section B', '102 Oak Rd, City B', 'Male', '12345678012', 'bob.miller@example.com', 'pass123', 'Guardian2', '09876543202', 'spic2.jpg', 123457, 'Not Verified', 'Offline', 'Pending', '2', 'ITWorks', 0, 'None', 'no', 'Technology', 'sis2.pdf', 'pending', 100, 2, 'ITWorks'),
('UID003', 'Clara', 'M', 'Davis', 'SID003', 'Institute of Technology', 'Diploma in Electrical Engineering Technology (DEET)', 'Section A', '103 Pine Ln, City C', 'Female', '12345678013', 'clara.davis@example.com', 'pass123', 'Guardian3', '09876543203', 'spic3.jpg', 123458, 'Not Verified', 'Offline', 'Pending', '1', 'BuildCo', 0, 'None', 'no', 'Technology', 'sis3.pdf', 'pending', 100, 3, 'BuildCo'),
('UID004', 'Daniel', 'N', 'Wilson', 'SID004', 'Institute of Technology', 'Diploma in Electronics Engineering Technology (DECET)', 'Section B', '104 Elm Dr, City D', 'Male', '12345678014', 'daniel.wilson@example.com', 'pass123', 'Guardian4', '09876543204', 'spic4.jpg', 123459, 'Not Verified', 'Offline', 'Pending', '2', 'SoftSys', 0, 'None', 'no', 'Technology', 'sis4.pdf', 'pending', 100, 4, 'SoftSys'),
('UID005', 'Eve', 'O', 'Taylor', 'SID005', 'Institute of Technology', 'Diploma in Information Communication Technology (DICT)', 'Section A', '105 Birch Ave, City E', 'Female', '12345678015', 'eve.taylor@example.com', 'pass123', 'Guardian5', '09876543205', 'spic5.jpg', 123460, 'Not Verified', 'Offline', 'Pending', '1', 'ManuFact', 0, 'None', 'no', 'Technology', 'sis5.pdf', 'pending', 100, 5, 'ManuFact'),
('UID006', 'Frank', 'P', 'Moore', 'SID006', 'Institute of Technology', 'Diploma in Mechanical Engineering Technology (DMET)', 'Section B', '106 Cedar St, City F', 'Male', '12345678016', 'frank.moore@example.com', 'pass123', 'Guardian6', '09876543206', 'spic6.jpg', 123461, 'Not Verified', 'Offline', 'Pending', '2', 'EngiCorp', 0, 'None', 'no', 'Technology', 'sis6.pdf', 'pending', 100, 6, 'EngiCorp'),
('UID007', 'Grace', 'Q', 'Anderson', 'SID007', 'Institute of Technology', 'Diploma in Office Management Technology (DOMT)', 'Section A', '107 Maple Rd, City G', 'Female', '12345678017', 'grace.anderson@example.com', 'pass123', 'Guardian7', '09876543207', 'spic7.jpg', 123462, 'Not Verified', 'Offline', 'Pending', '1', 'TechBit', 0, 'None', 'no', 'Technology', 'sis7.pdf', 'pending', 100, 7, 'TechBit'),
('UID008', 'Henry', 'R', 'Thomas', 'SID008', 'Institute of Technology', 'Diploma in Railway Engineering Technology (DRET)', 'Section B', '108 Spruce Ln, City H', 'Male', '12345678018', 'henry.thomas@example.com', 'pass123', 'Guardian8', '09876543208', 'spic8.jpg', 123463, 'Not Verified', 'Offline', 'Pending', '2', 'ConstructCo', 0, 'None', 'no', 'Technology', 'sis8.pdf', 'pending', 100, 8, 'ConstructCo'),
('UID009', 'Isabella', 'S', 'Jackson', 'SID009', 'Institute of Technology', 'Diploma in Civil Engineering Technology (DCvET)', 'Section A', '109 Ash Dr, City I', 'Female', '12345678019', 'isabella.jackson@example.com', 'pass123', 'Guardian9', '09876543209', 'spic9.jpg', 123464, 'Not Verified', 'Offline', 'Pending', '1', 'SoftPeak', 0, 'None', 'no', 'Technology', 'sis9.pdf', 'pending', 100, 9, 'SoftPeak'),
('UID010', 'Jack', 'T', 'White', 'SID010', 'Institute of Technology', 'Diploma in Computer Engineering Technology (DCET)', 'Section B', '110 Willow St, City J', 'Male', '12345678020', 'jack.white@example.com', 'pass123', 'Guardian10', '09876543210', 'spic10.jpg', 123465, 'Not Verified', 'Offline', 'Pending', '2', 'ManuPro', 0, 'None', 'no', 'Technology', 'sis10.pdf', 'pending', 100, 10, 'ManuPro');

-- 6. announcements
INSERT INTO announcements (title, content, portal, created_at, updated_at, created_by) VALUES
('Welcome Event', 'Join the welcome event for new interns.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1),
('Training Session', 'Training session starts next Monday.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 2),
('Holiday Notice', 'Office closed on December 25th.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 3),
('Meeting Reminder', 'Team meeting at 10 AM tomorrow.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 4),
('New Policy', 'New leave policy effective immediately.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 5),
('Tech Workshop', 'Workshop on technology this Friday.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 6),
('Report Deadline', 'Submit weekly reports by Friday.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 7),
('Event Reschedule', 'Event moved to next month.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 8),
('Safety Drill', 'Safety drill on Thursday morning.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 9),
('Farewell Party', 'Farewell party this Friday evening.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 10);

-- 7. chat_system
INSERT INTO chat_system (sender_id, receiver_id, messages, images, date_only, time_only, status, documents) VALUES
('UID001', 'CID001', 'Hello, how is the project going?', NULL, '2023-10-01', '12:00:00', 'Sent', NULL),
('CID001', 'UID001', 'It’s progressing well, thank you!', NULL, '2023-10-01', '12:05:00', 'Sent', NULL),
('UID002', 'CID002', 'Can you review my task?', NULL, '2023-10-02', '13:00:00', 'Sent', NULL),
('CID002', 'UID002', 'I’ll check it by end of day.', NULL, '2023-10-02', '13:05:00', 'Sent', NULL),
('UID003', 'CID003', 'Need to discuss my OJT hours.', NULL, '2023-10-03', '14:00:00', 'Sent', NULL),
('CID003', 'UID003', 'Let’s schedule a meeting tomorrow.', NULL, '2023-10-03', '14:05:00', 'Sent', NULL),
('UID004', 'CID004', 'I’ve submitted my report.', NULL, '2023-10-04', '15:00:00', 'Sent', NULL),
('CID004', 'UID004', 'Thanks, I’ll review it soon.', NULL, '2023-10-04', '15:05:00', 'Sent', NULL),
('UID005', 'CID005', 'Any updates on my MOA?', NULL, '2023-10-05', '16:00:00', 'Sent', NULL),
('CID005', 'UID005', 'It’s being processed.', NULL, '2023-10-05', '16:05:00', 'Sent', NULL);

-- 8. company_moa
INSERT INTO company_moa (supervisor_id, moa_file, company_name, date_uploaded, renewal_status) VALUES
(1, 'moa1.pdf', 'TechCorp', '2023-10-01 10:00:00', 'pending'),
(2, 'moa2.pdf', 'ITWorks', '2023-10-02 10:00:00', 'pending'),
(3, 'moa3.pdf', 'BuildCo', '2023-10-03 10:00:00', 'pending'),
(4, 'moa4.pdf', 'SoftSys', '2023-10-04 10:00:00', 'pending'),
(5, 'moa5.pdf', 'ManuFact', '2023-10-05 10:00:00', 'pending'),
(6, 'moa6.pdf', 'EngiCorp', '2023-10-06 10:00:00', 'pending'),
(7, 'moa7.pdf', 'TechBit', '2023-10-07 10:00:00', 'pending'),
(8, 'moa8.pdf', 'ConstructCo', '2023-10-08 10:00:00', 'pending'),
(9, 'moa9.pdf', 'SoftPeak', '2023-10-09 10:00:00', 'pending'),
(10, 'moa10.pdf', 'ManuPro', '2023-10-10 10:00:00', 'pending');


-- 14. endorsement_documents
INSERT INTO endorsement_documents (document_name, document_type, uploaded_path, student_id, upload_date, status, medical_at_campus, remarks) VALUES
('Resume_Alice', 'Resume', 'docs/resume1.pdf', 1, '2023-10-01 10:00:00', 'pending', 'YES', 'Awaiting review'),
('Resume_Bob', 'Resume', 'docs/resume2.pdf', 2, '2023-10-02 10:00:00', 'pending', 'YES', 'Awaiting review'),
('Resume_Clara', 'Resume', 'docs/resume3.pdf', 3, '2023-10-03 10:00:00', 'pending', 'YES', 'Awaiting review'),
('Resume_Daniel', 'Resume', 'docs/resume4.pdf', 4, '2023-10-04 10:00:00', 'pending', 'YES', 'Awaiting review'),
('Resume_Eve', 'Resume', 'docs/resume5.pdf', 5, '2023-10-05 10:00:00', 'pending', 'YES', 'Awaiting review'),
('Resume_Frank', 'Resume', 'docs/resume6.pdf', 6, '2023-10-06 10:00:00', 'pending', 'YES', 'Awaiting review'),
('Resume_Grace', 'Resume', 'docs/resume7.pdf', 7, '2023-10-07 10:00:00', 'pending', 'YES', 'Awaiting review'),
('Resume_Henry', 'Resume', 'docs/resume8.pdf', 8, '2023-10-08 10:00:00', 'pending', 'YES', 'Awaiting review'),
('Resume_Isabella', 'Resume', 'docs/resume9.pdf', 9, '2023-10-09 10:00:00', 'pending', 'YES', 'Awaiting review'),
('Resume_Jack', 'Resume', 'docs/resume10.pdf', 10, '2023-10-10 10:00:00', 'pending', 'YES', 'Awaiting review');

-- 15. faqs
INSERT INTO faqs (question, answer, portal, created_at, updated_at, created_by) VALUES
('How to submit OJT hours?', 'Follow the guide in the student portal.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1),
('What is the MOA process?', 'Submit documents via the coordinator portal.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 2),
('How to update my profile?', 'Navigate to settings in your portal.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 3),
('When are reports due?', 'Weekly reports due every Friday.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 4),
('How to contact my supervisor?', 'Use the chat system in the portal.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 5),
('What are the required hours?', 'Minimum of 100 hours for OJT.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 6),
('How to join meetings?', 'Check the meetings tab for links.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 7),
('What is verification?', 'Confirm your email via the portal.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 8),
('How to upload documents?', 'Use the upload feature in the portal.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 9),
('What is the OJT portal?', 'System for managing internship tasks.', 'All', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 10);



-- 17. internship_experience
INSERT INTO internship_experience (student_id, document_name, uploaded_path, upload_date, status, remarks) VALUES
(1, 'Report_Alice', 'reports/report1.pdf', '2023-10-01 10:00:00', 'pending', 'Awaiting review'),
(2, 'Report_Bob', 'reports/report2.pdf', '2023-10-02 10:00:00', 'pending', 'Awaiting review'),
(3, 'Report_Clara', 'reports/report3.pdf', '2023-10-03 10:00:00', 'pending', 'Awaiting review'),
(4, 'Report_Daniel', 'reports/report4.pdf', '2023-10-04 10:00:00', 'pending', 'Awaiting review'),
(5, 'Report_Eve', 'reports/report5.pdf', '2023-10-05 10:00:00', 'pending', 'Awaiting review'),
(6, 'Report_Frank', 'reports/report6.pdf', '2023-10-06 10:00:00', 'pending', 'Awaiting review'),
(7, 'Report_Grace', 'reports/report7.pdf', '2023-10-07 10:00:00', 'pending', 'Awaiting review'),
(8, 'Report_Henry', 'reports/report8.pdf', '2023-10-08 10:00:00', 'pending', 'Awaiting review'),
(9, 'Report_Isabella', 'reports/report9.pdf', '2023-10-09 10:00:00', 'pending', 'Awaiting review'),
(10, 'Report_Jack', 'reports/report10.pdf', '2023-10-10 10:00:00', 'pending', 'Awaiting review');

-- 18. intern_deployments
INSERT INTO intern_deployments (intern_id, department, supervisor_id, deployed_by, deployment_date, created_at, updated_at, required_hours) VALUES
(1, 'Engineering', 1, 1, '2023-10-01', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 100),
(2, 'IT', 2, 2, '2023-10-02', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 100),
(3, 'Construction', 3, 3, '2023-10-03', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 100),
(4, 'Software', 4, 4, '2023-10-04', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 100),
(5, 'Manufacturing', 5, 5, '2023-10-05', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 100),
(6, 'Engineering', 6, 6, '2023-10-06', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 100),
(7, 'IT', 7, 7, '2023-10-07', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 100),
(8, 'Construction', 8, 8, '2023-10-08', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 100),
(9, 'Software', 9, 9, '2023-10-09', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 100),
(10, 'Manufacturing', 10, 10, '2023-10-10', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 100);

-- 19. meetings
INSERT INTO meetings (meeting_type, link, passcode, meeting_date, meeting_time, agenda, created_by, created_at, portal) VALUES
('Zoom Meeting', 'zoom.us/j/1234567890', 'pass123', '2023-10-01', '10:00:00', 'Discuss OJT progress', 1, CURRENT_TIMESTAMP, 'all'),
('Zoom Meeting', 'zoom.us/j/1234567891', 'pass123', '2023-10-02', '11:00:00', 'Review document submissions', 2, CURRENT_TIMESTAMP, 'all'),
('Zoom Meeting', 'zoom.us/j/1234567892', 'pass123', '2023-10-03', '12:00:00', 'Plan weekly tasks', 3, CURRENT_TIMESTAMP, 'all'),
('Zoom Meeting', 'zoom.us/j/1234567893', 'pass123', '2023-10-04', '13:00:00', 'Evaluate student progress', 4, CURRENT_TIMESTAMP, 'all'),
('Zoom Meeting', 'zoom.us/j/1234567894', 'pass123', '2023-10-05', '14:00:00', 'Discuss MOA updates', 5, CURRENT_TIMESTAMP, 'all'),
('Zoom Meeting', 'zoom.us/j/1234567895', 'pass123', '2023-10-06', '15:00:00', 'Team status update', 6, CURRENT_TIMESTAMP, 'all'),
('Zoom Meeting', 'zoom.us/j/1234567896', 'pass123', '2023-10-07', '16:00:00', 'Feedback session', 7, CURRENT_TIMESTAMP, 'all'),
('Zoom Meeting', 'zoom.us/j/1234567897', 'pass123', '2023-10-08', '17:00:00', 'Review internship progress', 8, CURRENT_TIMESTAMP, 'all'),
('Zoom Meeting', 'zoom.us/j/1234567898', 'pass123', '2023-10-09', '18:00:00', 'Plan next steps', 9, CURRENT_TIMESTAMP, 'all'),
('Zoom Meeting', 'zoom.us/j/1234567899', 'pass123', '2023-10-10', '19:00:00', 'Wrap-up meeting', 10, CURRENT_TIMESTAMP, 'all');

-- 20. moa_form
INSERT INTO moa_form (company_name, company_address, nature_of_business, contact_person_name, company_position, email_address, start_date_validity, end_date_validity, created_at, supervisor_id) VALUES
('TechCorp', '123 Tech St, City A', 'Engineering', 'John Wilson', 'Manager', 'john.wilson@example.com', '2023-01-01', '2024-01-01', CURRENT_TIMESTAMP, 1),
('ITWorks', '456 IT Ave, City B', 'IT', 'Mary Thompson', 'Engineer', 'mary.thompson@example.com', '2023-01-02', '2024-01-02', CURRENT_TIMESTAMP, 2),
('BuildCo', '789 Build Rd, City C', 'Construction', 'Robert Garcia', 'Supervisor', 'robert.garcia@example.com', '2023-01-03', '2024-01-03', CURRENT_TIMESTAMP, 3),
('SoftSys', '101 Soft Ln, City D', 'Software', 'Susan Martinez', 'Engineer', 'susan.martinez@example.com', '2023-01-04', '2024-01-04', CURRENT_TIMESTAMP, 4),
('ManuFact', '202 Manu Tech St, City E', 'Manufacturing', 'Thomas Rodriguez', 'Technician', 'thomas.rodriguez@example.com', '2023-01-05', '2024-01-05', CURRENT_TIMESTAMP, 5),
('EngTech', '303 EngTech Way, City F', 'Engineering', 'Linda Lee', 'Manager', 'linda.lee@example.com', '2023-01-06', '2024-01-06', CURRENT_TIMESTAMP, 6),
('TechBit', '404 ITTech Pl, City G', 'IT', 'James Hernandez', 'Engineer', 'james.hernandez@example.com', '2023-01-07', '2024-01-07', CURRENT_TIMESTAMP, 7),
('ConstructCo', '505 Const Tech St, City H', 'Construction', 'Patricia Walker', 'Engineer', 'patricia.walker@example.com', '2023-01-08', '2024-01-08', CURRENT_TIMESTAMP, 8),
('SoftTech', '606 SoftTech Rd, City I', 'Software', 'William Lopez', 'Engineer', 'william.lopez@example.com', '2023-01-09', '2024-01-09', CURRENT_TIMESTAMP, 9),
('TechManu', '707 Tech Ave, City J', 'Manufacturing', 'Barbara Gonzalez', 'Supervisor', 'barbara.gonzalez@example.com', '2023-01-10', '2024-01-10', CURRENT_TIMESTAMP, 10);


-- 22. new_moa_processing
INSERT INTO new_moa_processing (id, student_id, moa_document, status, request_date, created_at, updated_at) VALUES
('1', 1, 'moa_docs/moa1.pdf', 'checking_info', 'pending', '2023-10-01', '10:00:00', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('2', 2, 'moa_docs/martinez.pdf', 'checking_info', 'pending', '2023-10-02', '10:00:10', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('3', 3, 'moa_docs/garcia.pdf', 'checking_info', 'pending', '2023-10-03', '10:00:00', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('4', 4, 'moa_docs/thomas.pdf', 'checking_info', 'pending', '2023-10-04', '10:00:00', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('5', 5, 'moa_docs/barbara.pdf', 'checking_info', 'pending', '2023-10-05', '10:00:00', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('6', 6, 'moa_docs/hernandez.pdf', 'checking_info', 'pending', '2023-10-06', '10:00:00', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('7', 7, 'moa_docs/lopezz.pdf', 'checking_info', 'pending', '2023-10-07', '10:00:00', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('8', 8, 'moa_docs/walkers.pdf', 'checking_info', 'pending', '2023-10-08', '10:00:00', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('9', 9, 'moa_docs/loopezz.pdf', 'checking_info', 'pending', '2023-10-09', '10:00:00', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('10', 10, 'moa_docs/mooodo.pdf', 'checking_info', 'pending', '2023-10-10', '10:00:00', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- 23. ojt_requirements
INSERT INTO ojt_requirements (rt_id, student_id, document_name, document_fileName, document_location, status) VALUES
(1, 1, 'Resume_Alice', 'resume1.pdf', 'docs/resume1.pdf', 'Pending'),
(2, 2, 'Resume_Bob', 'resume2.pdf', 'docs/resume2.pdf', 'Pending'),
(3, 3, 'Resume_Clarra', 'resume3.pdf', 'docs/resume3.pdf', 'Pending'),
(4, 4, 'Resume_Daaniel', 'resume4.pdf', 'docs/resume4.pdf', 'Pending'),
(5, 5, 'Resume_Eve', 'resume5.pdf', 'docs/resume.pdf', 'Pending'),
(6, 6, 'Resume_Frrank', '6', 'resume6.pdf', 'docs/resume6.pdf', 'Pending'),
(7, 7, 'Resume_Grrace', '7', 'resume_resume.pdf', 'docs/resume.pdf', 'Pending'),
(8, '8', 'Resume_Hennry', 'Pending', 'resume_doc.pdf', 'pending', 'Pending'),
(9, '9', 'Resume_Issabella', NULL, 'resume_resume_doc.pdf', NULL, '10', 'Pending'),
(10, '10', NULL, NULL, NULL, NULL, 'Pending');

-- 25. photo_documentation
INSERT INTO photo_documentation (student_id, id, document_name, full_name, uploaded_path, status, feedback_by_id, supervisor_id, feedback, created_at, updated_at, remarks) VALUES
(1, '1', 'Photo_Alice', 'Alice Johnson', 'photos/photo1.jpg', 'pending', NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Awaiting review'),
(2, '2', 'Photo_Bob', 'Bob Miller', 'photos/photo2.jpg', 'pending', NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Awaiting review'),
(3, '3', 'Photo_Clara', 'Clara Davis', 'photos/photo3.jpg', 'pending', NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Awaiting review'),
(4, '4', 'Photo_Daniel', 'Daniel Wilson', 'photos/photo4.jpg', 'pending', NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Awaiting review'),
(5, '5', 'Photo_Eve', 'Eve Taylor', 'photos/photo5.jpg', 'pending', NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Awaiting review'),
(6, '6', 'Photo_Frank', 'Frank Moore', 'photos/photo6.jpg', 'pending', NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Awaiting review'),
(7, '7', 'Photo_Grace', 'Grace Anderson', 'photos/photo7.jpg', 'pending', NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Awaiting review'),
('8', '8', 'Photo_Henry', 'Henry Thomas', 'photos/photo8.jpg', 'pending', NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Awaiting review'),
('9', '9', 'Photo_Isabella', 'Isabella Jackson', 'photos/photo9.jpg', 'pending', NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Awaiting review'),
('10', '10', 'Photo_Jack', 'Jack White', 'photos/photo10.jpg', 'pending', NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Awaiting review');




-- 34. weekly_accomplishment
INSERT INTO weekly_accomplishment (stud_id, uniqueID, week_number, date, time, accomplishment, coworkers, working_hours, created_at) VALUES
(1, 'UID001', 1, '2023-10-01', '08:00:00', 'Completed site survey', 'Team A', 8, CURRENT_TIMESTAMP),
(2, 'UID002', 1, '2023-10-02', '08:00:00', 'Developed software module', 'Team B', 8, CURRENT_TIMESTAMP),
(3, 'UID003', 1, '2023-10-03', '08:00:00', 'Wired electrical circuit', 'Team C', 8, CURRENT_TIMESTAMP),
(4, 'UID004', 1, '2023-10-04', '08:00:00', 'Built sensor device', 'Team D', 8, CURRENT_TIMESTAMP),
(5, 'UID005', 1, '2023-10-05', '08:00:00', 'Configured network system', 'Team E', 8, CURRENT_TIMESTAMP),
(6, 'UID006', 1, '2023-10-06', '08:00:00', 'Assembled mechanical part', 'Team F', 8, CURRENT_TIMESTAMP),
(7, 'UID007', 1, '2023-10-07', '08:00:00', 'Organized office files', 'Team G', 8, CURRENT_TIMESTAMP),
(8, 'UID008', 1, '2023-10-08', '08:00:00', 'Inspected railway tracks', 'Team H', 8, CURRENT_TIMESTAMP),
(9, 'UID009', 1, '2023-10-09', '08:00:00', 'Designed structural plan', 'Team I', 8, CURRENT_TIMESTAMP),
(10, 'UID010', 1, '2023-10-10', '08:00:00', 'Developed software script', 'Team J', 8, CURRENT_TIMESTAMP);