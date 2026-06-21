UPDATE users
SET organization_id = (
    SELECT id FROM organizations ORDER BY id ASC LIMIT 1
)
WHERE organization_id IS NULL;

UPDATE voters
SET organization_id = (
    SELECT id FROM organizations ORDER BY id ASC LIMIT 1
)
WHERE organization_id IS NULL;

UPDATE elections
SET organization_id = (
    SELECT id FROM organizations ORDER BY id ASC LIMIT 1
)
WHERE organization_id IS NULL;