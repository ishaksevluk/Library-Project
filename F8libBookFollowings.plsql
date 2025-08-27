-----------------------------------------------------------------------------
--
--  Logical unit: F8libBookFollowings
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
   newrec_ IN OUT f8lib_book_followings_tab%ROWTYPE,
   indrec_ IN OUT Indicator_Rec,
   attr_   IN OUT VARCHAR2 )
IS
BEGIN
   --(+)20250725 gamze (basla)
   newrec_.book_following_id := 0;
   super(newrec_, indrec_, attr_);
  --(+)20250725 gamze (bitti)
END Check_Insert___;



@Override
PROCEDURE Insert___ (
   objid_      OUT    VARCHAR2,
   objversion_ OUT    VARCHAR2,
   newrec_     IN OUT f8lib_book_followings_tab%ROWTYPE,
   attr_       IN OUT VARCHAR2 )
IS
   CURSOR get_max_book_following_id IS 
      SELECT MAX(book_following_id)
      FROM f8lib_book_followings_tab;
   max_id_ NUMBER;
BEGIN
    OPEN get_max_book_following_id;
   FETCH get_max_book_following_id INTO max_id_;
   CLOSE get_max_book_following_id;
   newrec_.book_following_id:= NVL(max_id_,0)+1;
   Client_SYS.Add_To_Attr('USER_ID',   newrec_.book_following_id,       attr_);
   Client_SYS.Add_To_Attr('FOLLOWED_DATE',   SYSDATE,       attr_);
    --(+)20250725 gamze (basla)
   super(objid_, objversion_, newrec_, attr_);
    --(+)20250725 gamze (bitti)
END Insert___;

-------------------- LU SPECIFIC PRIVATE METHODS ----------------------------


-------------------- LU SPECIFIC PROTECTED METHODS --------------------------


-------------------- LU SPECIFIC PUBLIC METHODS -----------------------------