## SQL Version Control

   ***Only use the latest sql file***
   
- `awords_v4.1.sql` [`current`]
    
    - `active_sessions` : The column `type` is removed and `data` and `time`
       columns are added as part of migration to DB based session
    - `uploaded_files` table is added as par of part of migration to DB based session

- `awords_v4.0.sql` [`obsolete`]

    - `pending_projects` table is added
    - `projects` : The column `seen` is added
    - `last_projects` : The column `seen` is removed
