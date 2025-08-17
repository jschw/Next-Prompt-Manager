-- Table: prompts
CREATE TABLE prompts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prompt TEXT NOT NULL,
    title VARCHAR(1024), -- was description
    topic VARCHAR(255),
    tags VARCHAR(512),
    favorite BOOLEAN DEFAULT FALSE,
    stage ENUM('draft', 'review', 'final', 'archived') DEFAULT 'draft',
    llm_params TEXT,
    version INT DEFAULT 1,
    is_public BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: prompt_versions
CREATE TABLE prompt_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prompt_id INT NOT NULL,
    version INT NOT NULL,
    prompt TEXT NOT NULL,
    title VARCHAR(1024), -- was description
    topic VARCHAR(255),
    tags VARCHAR(512),
    favorite BOOLEAN,
    stage ENUM('draft', 'review', 'final', 'archived'),
    llm_params TEXT,
    change_desc VARCHAR(1024),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prompt_id) REFERENCES prompts(id) ON DELETE CASCADE
);

-- Table: access_tokens
CREATE TABLE access_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token CHAR(64) NOT NULL UNIQUE,
    prompt_id INT,
    is_dashboard_token BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (prompt_id) REFERENCES prompts(id) ON DELETE CASCADE
);

INSERT INTO access_tokens (token, prompt_id, is_dashboard_token, expires_at) VALUES (3141593, null, 1, null);

-- Table: tags (optional)
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64) NOT NULL UNIQUE
);

CREATE TABLE prompt_tags (
    prompt_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (prompt_id, tag_id),
    FOREIGN KEY (prompt_id) REFERENCES prompts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Indexes for fulltext search (MySQL 5.6+)
ALTER TABLE prompts ADD FULLTEXT INDEX ft_prompt_title_tags (prompt, title, tags);