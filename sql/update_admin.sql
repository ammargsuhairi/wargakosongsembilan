USE dhsbord;
UPDATE users SET password = '$2y$10$DMrAeE57mXkfj42gCDEfQudruDb3z/.Z3dwOFLqat0H35WbVlXRBq', fullname='Administrator' WHERE username='admin';
SELECT id, username, password, fullname FROM users;
