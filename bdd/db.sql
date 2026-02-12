-- ----------------------------------------------------------
-- Script POSTGRESQL pour mcd
-- ----------------------------------------------------------


-- ----------------------------
-- Table: Artist
-- ----------------------------
CREATE TABLE Artist (
                        Artist_ID VARCHAR(50) NOT NULL,
                        Artist_Pseudo VARCHAR(100) NOT NULL,
                        Artist_Image VARCHAR(50),
                        CONSTRAINT Artist_PK PRIMARY KEY (Artist_ID)
);


-- ----------------------------
-- Table: MUSIC
-- ----------------------------
CREATE TABLE MUSIC (
                       Music_ID VARCHAR(50) NOT NULL,
                       Music_Title VARCHAR(150) NOT NULL,
                       Music_Duration INTEGER NOT NULL,
                       Music_Place INTEGER NOT NULL,
                       CONSTRAINT MUSIC_PK PRIMARY KEY (Music_ID)
);


-- ----------------------------
-- Table: Album
-- ----------------------------
CREATE TABLE Album (
                       Album_ID VARCHAR(50) NOT NULL,
                       Album_Name VARCHAR(150) NOT NULL,
                       Album_Date VARCHAR(50) NOT NULL,
                       Album_Image VARCHAR(50) NOT NULL,
                       Album_Type VARCHAR(50) NOT NULL,
                       CONSTRAINT Album_PK PRIMARY KEY (Album_ID)
);


-- ----------------------------
-- Table: GENRE
-- ----------------------------
CREATE TABLE GENRE (
                       Genre_ID VARCHAR(50) NOT NULL,
                       Genre_Name VARCHAR(50) NOT NULL,
                       CONSTRAINT GENRE_PK PRIMARY KEY (Genre_ID)
);


-- ----------------------------
-- Table: USER
-- ----------------------------
CREATE TABLE USERS (
                      User_ID VARCHAR(50) NOT NULL,
                      User_Mail VARCHAR(50) NOT NULL,
                      User_Name VARCHAR(50) NOT NULL,
                      User_Surname VARCHAR(50) NOT NULL,
                      User_birthdate DATE NOT NULL,
                      User_Pseudo VARCHAR(50) NOT NULL,
                      User_Image VARCHAR(50) NOT NULL,
                      User_Password VARCHAR(255) NOT NULL,
                      CONSTRAINT USER_PK PRIMARY KEY (User_ID)
);


-- ----------------------------
-- Table: Possede
-- ----------------------------
CREATE TABLE Possede (
                         Genre_ID VARCHAR(50) NOT NULL,
                         Music_ID VARCHAR(50) NOT NULL,
                         CONSTRAINT Possede_PK PRIMARY KEY (Genre_ID, Music_ID),
                         CONSTRAINT Possede_Genre_ID_FK FOREIGN KEY (Genre_ID) REFERENCES GENRE (Genre_ID),
                         CONSTRAINT Possede_Music_ID_FK FOREIGN KEY (Music_ID) REFERENCES MUSIC (Music_ID)
);


-- ----------------------------
-- Table: Playlist
-- ----------------------------
CREATE TABLE Playlist (
                          Playlist_ID VARCHAR(50) NOT NULL,
                          Playlist_Name VARCHAR(50) NOT NULL,
                          Playlist_Creation_Date DATE NOT NULL,
                          User_ID VARCHAR(50) NOT NULL,
                          CONSTRAINT Playlist_PK PRIMARY KEY (Playlist_ID),
                          CONSTRAINT Playlist_User_ID_FK FOREIGN KEY (User_ID) REFERENCES USERS (User_ID)
);


-- ----------------------------
-- Table: Compose
-- ----------------------------
CREATE TABLE Compose (
                         Album_ID VARCHAR(50) NOT NULL,
                         Artist_ID VARCHAR(50) NOT NULL,
                         CONSTRAINT Compose_PK PRIMARY KEY (Album_ID, Artist_ID),
                         CONSTRAINT Compose_Album_ID_FK FOREIGN KEY (Album_ID) REFERENCES Album (Album_ID),
                         CONSTRAINT Compose_Artist_ID_FK FOREIGN KEY (Artist_ID) REFERENCES Artist (Artist_ID)
);


-- ----------------------------
-- Table: Creer
-- ----------------------------
CREATE TABLE Creer (
                       Music_ID VARCHAR(50) NOT NULL,
                       Artist_ID VARCHAR(50) NOT NULL,
                       CONSTRAINT Creer_PK PRIMARY KEY (Music_ID, Artist_ID),
                       CONSTRAINT Creer_Music_ID_FK FOREIGN KEY (Music_ID) REFERENCES MUSIC (Music_ID),
                       CONSTRAINT Creer_Artist_ID_FK FOREIGN KEY (Artist_ID) REFERENCES Artist (Artist_ID)
);


-- ----------------------------
-- Table: Contient
-- ----------------------------
CREATE TABLE Contient (
                          Music_ID VARCHAR(50) NOT NULL,
                          Album_ID VARCHAR(50) NOT NULL,
                          CONSTRAINT Contient_PK PRIMARY KEY (Music_ID, Album_ID),
                          CONSTRAINT Contient_Music_ID_FK FOREIGN KEY (Music_ID) REFERENCES MUSIC (Music_ID),
                          CONSTRAINT Contient_Album_ID_FK FOREIGN KEY (Album_ID) REFERENCES Album (Album_ID)
);


-- ----------------------------
-- Table: Aime
-- ----------------------------
CREATE TABLE Aime (
                      Music_ID VARCHAR(50) NOT NULL,
                      User_ID VARCHAR(50) NOT NULL,
                      CONSTRAINT Aime_PK PRIMARY KEY (Music_ID, User_ID),
                      CONSTRAINT Aime_Music_ID_FK FOREIGN KEY (Music_ID) REFERENCES MUSIC (Music_ID),
                      CONSTRAINT Aime_User_ID_FK FOREIGN KEY (User_ID) REFERENCES USERS (User_ID)
);


-- ----------------------------
-- Table: Appartient
-- ----------------------------
CREATE TABLE Appartient (
                            Music_ID VARCHAR(50) NOT NULL,
                            Playlist_ID VARCHAR(50) NOT NULL,
                            CONSTRAINT Appartient_PK PRIMARY KEY (Music_ID, Playlist_ID),
                            CONSTRAINT Appartient_Music_ID_FK FOREIGN KEY (Music_ID) REFERENCES MUSIC (Music_ID),
                            CONSTRAINT Appartient_Playlist_ID_FK FOREIGN KEY (Playlist_ID) REFERENCES Playlist (Playlist_ID)
);
