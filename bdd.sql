CREATE TABLE Users(
                      id_user INT AUTO_INCREMENT,
                      password VARCHAR(50)  NOT NULL,
                      username VARCHAR(50)  NOT NULL,
                      email VARCHAR(50)  NOT NULL,
                      PRIMARY KEY(id_user),
                      UNIQUE(username)
);

CREATE TABLE Form(
                     id_form INT AUTO_INCREMENT,
                     name VARCHAR(50)  NOT NULL,
                     id_user INT NOT NULL,
                     PRIMARY KEY(id_form),
                     FOREIGN KEY(id_user) REFERENCES Users(id_user)
);

CREATE TABLE Question(
                         id_question INT AUTO_INCREMENT,
                         title VARCHAR(50)  NOT NULL,
                         order_ INT NOT NULL,
                         id_form INT NOT NULL,
                         PRIMARY KEY(id_question),
                         FOREIGN KEY(id_form) REFERENCES Form(id_form)
);

CREATE TABLE Selection(
                          id_question INT,
                          PRIMARY KEY(id_question),
                          FOREIGN KEY(id_question) REFERENCES Question(id_question)
);

CREATE TABLE Grade(
                      id_question INT,
                      PRIMARY KEY(id_question),
                      FOREIGN KEY(id_question) REFERENCES Question(id_question)
);

CREATE TABLE FreeText(
                         id_question INT,
                         PRIMARY KEY(id_question),
                         FOREIGN KEY(id_question) REFERENCES Question(id_question)
);

CREATE TABLE Choice(
                       id_choice INT AUTO_INCREMENT,
                       title VARCHAR(50)  NOT NULL,
                       id_question INT NOT NULL,
                       PRIMARY KEY(id_choice),
                       FOREIGN KEY(id_question) REFERENCES Selection(id_question)
);

CREATE TABLE Answer(
                       id_answer INT AUTO_INCREMENT,
                       id_user INT NOT NULL,
                       id_question INT NOT NULL,
                       PRIMARY KEY(id_answer),
                       FOREIGN KEY(id_user) REFERENCES Users(id_user),
                       FOREIGN KEY(id_question) REFERENCES Question(id_question)
);

CREATE TABLE Ans_Selection(
                              id_answer INT,
                              id_choice INT NOT NULL,
                              PRIMARY KEY(id_answer),
                              FOREIGN KEY(id_answer) REFERENCES Answer(id_answer),
                              FOREIGN KEY(id_choice) REFERENCES Choice(id_choice)
);

CREATE TABLE Ans_Grade(
                          id_answer INT,
                          score INT NOT NULL,
                          PRIMARY KEY(id_answer),
                          FOREIGN KEY(id_answer) REFERENCES Answer(id_answer)
);

CREATE TABLE Ans_FreeText(
                             id_answer INT,
                             text TEXT NOT NULL,
                             PRIMARY KEY(id_answer),
                             FOREIGN KEY(id_answer) REFERENCES Answer(id_answer)
);