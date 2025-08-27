-----------------------------------------------------------------------------
--
--  Logical unit: F8libAuthors
--  Component:    F8LIB
--
--  IFS Developer Studio Template Version 3.0
--
--  Date    Sign    History
--  ------  ------  ---------------------------------------------------------
-----------------------------------------------------------------------------

layer Core;

-------------------- PUBLIC DECLARATIONS ------------------------------------


-------------------- PRIVATE DECLARATIONS -----------------------------------


-------------------- LU SPECIFIC IMPLEMENTATION METHODS ---------------------
@Override
PROCEDURE Check_Insert___ (
   newrec_ IN OUT f8lib_authors_tab%ROWTYPE,
   indrec_ IN OUT Indicator_Rec,
   attr_   IN OUT VARCHAR2 )
IS
BEGIN
   --(+)20250725 ishak (basla)
   newrec_.author_id := 0;
   --(+)20250725 ishak (bitir)
   super(newrec_, indrec_, attr_);
END Check_Insert___;

@Override
PROCEDURE Insert___ (
   objid_      OUT    VARCHAR2,
   objversion_ OUT    VARCHAR2,
   newrec_     IN OUT f8lib_authors_tab%ROWTYPE,
   attr_       IN OUT VARCHAR2 )
IS
   CURSOR get_max_author_id IS 
      SELECT MAX(author_id)
      FROM f8lib_authors_tab;
   max_id_ NUMBER;
BEGIN
   --(+)20250725 ishak (basla)
   OPEN get_max_author_id;
   FETCH get_max_author_id INTO max_id_;
   CLOSE get_max_author_id;
   newrec_.author_id:= NVL(max_id_,0)+1;
   Client_SYS.Add_To_Attr('AUTHOR_ID',   newrec_.author_id,       attr_);
   --(+)20250725 ishak (bitir)
   super(objid_, objversion_, newrec_, attr_);
END Insert___;


-------------------- LU SPECIFIC PRIVATE METHODS ----------------------------


-------------------- LU SPECIFIC PROTECTED METHODS --------------------------


-------------------- LU SPECIFIC PUBLIC METHODS -----------------------------

