## SQL Version Control

   ***Only use the latest sql file***
   
- `adact_v4.4.sql` [`current`]

    - `projects` : `project_started` and `project_finished` columns added

- `adact_v4.3.sql`

    Some minor changes

- `adact_v4.2.sql`

    - `uploaded_files` : The column `date` is added to handle junk files (uploaded files are deleted after 7 days)
    - `pending_projects` : The column `edit_mode` is added as part of the implementation of editing the projects

- `awords_v4.1.sql`

    - `active_sessions` : The column `type` is removed and `data` and `time`
       columns are added as part of migration to DB based session
    - `uploaded_files` table is added as par of part of migration to DB based session

- `awords_v4.0.sql` [`obsolete`]

    - `pending_projects` table is added
    - `projects` : The column `seen` is added
    - `last_projects` : The column `seen` is removed
