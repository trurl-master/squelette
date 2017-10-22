
-----------------------------------------------------------------------
-- config
-----------------------------------------------------------------------

DROP TABLE IF EXISTS [config];

CREATE TABLE [config]
(
    [key] VARCHAR(64) NOT NULL,
    [value] MEDIUMTEXT,
    [type] VARCHAR(64) NOT NULL,
    [label] VARCHAR(64),
    [note] VARCHAR(64),
    [group] VARCHAR(32),
    [params] VARCHAR(1024),
    PRIMARY KEY ([key]),
    UNIQUE ([key])
);

-----------------------------------------------------------------------
-- meta
-----------------------------------------------------------------------

DROP TABLE IF EXISTS [meta];

CREATE TABLE [meta]
(
    [id] INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    [name] VARCHAR(255) NOT NULL,
    [title] VARCHAR(255) NOT NULL,
    [description] VARCHAR(1024),
    [keywords] VARCHAR(1024),
    [custom] MEDIUMTEXT,
    UNIQUE ([name]),
    UNIQUE ([id])
);

-----------------------------------------------------------------------
-- user
-----------------------------------------------------------------------

DROP TABLE IF EXISTS [user];

CREATE TABLE [user]
(
    [id] INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    [email] VARCHAR(255) NOT NULL,
    [password] VARCHAR(255) NOT NULL,
    [first_name] VARCHAR(255) NOT NULL,
    [last_name] VARCHAR(255) NOT NULL,
    [dt_created] TIMESTAMP NOT NULL,
    [dt_last_signin] TIMESTAMP,
    [hybridauth_provider_name] VARCHAR(255),
    [hybridauth_provider_uid] VARCHAR(255),
    [init] CHAR(128),
    [restore] CHAR(128),
    [privilege] INTEGER(255) DEFAULT 1 NOT NULL,
    UNIQUE ([id])
);
