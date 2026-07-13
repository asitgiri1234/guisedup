-- 001_enable_pgvector.sql
-- Enables the pgvector extension on the target database.
-- Idempotent: safe to run multiple times.
--
-- Prerequisite: the pgvector extension must be installed on the PostgreSQL server
-- (vector.dll in <PGROOT>/lib and vector.control in <PGROOT>/share/extension).
-- On this environment pgvector 0.8.5 was built from source against PostgreSQL 18.
--
-- Usage:
--   psql -h 127.0.0.1 -U postgres -d guisedup -f sql/001_enable_pgvector.sql

CREATE EXTENSION IF NOT EXISTS vector;

-- Verify (prints the installed extension version):
--   SELECT extname, extversion FROM pg_extension WHERE extname = 'vector';
