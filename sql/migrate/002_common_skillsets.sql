insert into trustbp_dev.common_skillsets 
SELECT 
id, 
created_at, 
updated_at, 
category,  --category
name,      -- name
skill_category_id, --skill_category_id
skill_type_id,  -- skill_type_id
null created_by, 
null updated_by  -- added_by


 FROM trust.common_skillsets

       