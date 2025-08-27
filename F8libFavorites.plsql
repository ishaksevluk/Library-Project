-----------------------------------------------------------------------------
--
--  Logical unit: F8libFavorites
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
   newrec_ IN OUT f8lib_favorites_tab%ROWTYPE,
   indrec_ IN OUT Indicator_Rec,
   attr_   IN OUT VARCHAR2 )
IS
BEGIN
   --Add pre-processing code here
   super(newrec_, indrec_, attr_);
   --Add post-processing code here
END Check_Insert___;
@Override
PROCEDURE Insert___ (
   objid_      OUT    VARCHAR2,
   objversion_ OUT    VARCHAR2,
   newrec_     IN OUT f8lib_favorites_tab%ROWTYPE,
   attr_       IN OUT VARCHAR2 )
IS
BEGIN
   
   --Add pre-processing code here
   Client_SYS.Add_To_Attr('FAVORITE_DATE',   SYSDATE,       attr_);
   super(objid_, objversion_, newrec_, attr_);
   --Add post-processing code here
END Insert___;







-------------------- LU SPECIFIC PRIVATE METHODS ----------------------------


-------------------- LU SPECIFIC PROTECTED METHODS --------------------------


-------------------- LU SPECIFIC PUBLIC METHODS -----------------------------

PROCEDURE Add_Favorites(book_id_     NUMBER,
                           user_id_     NUMBER,
                           is_favorite_ NUMBER) 
IS
 info_       VARCHAR2(32000);
 objid_      VARCHAR2(32000);
 objversion_ VARCHAR2(32000);
 attr_       VARCHAR2(32000);
 CURSOR get_objs IS 
 SELECT objid, objversion
 FROM f8lib_favorites f
 WHERE f .user_id= user_id_
 AND   f .book_id = book_id_;
BEGIN
   IF is_favorite_ = 1 THEN 
      IF F8lib_Favorites_API.Exists(user_id_, book_id_) THEN
         OPEN get_objs;
         FETCH get_objs INTO objid_,objversion_;
         CLOSE get_objs;
         Client_SYS.Clear_Attr(attr_);
         Client_SYS.Add_To_Attr('IS_FAVORITE',  'TRUE' ,  attr_);
         F8lib_Favorites_API.Modify__(info_ ,
                               objid_,
                               objversion_,
                               attr_,
                               'DO');     
      ELSE
         Client_SYS.Clear_Attr(attr_);
         Client_SYS.Add_To_Attr('USER_ID',user_id_,attr_);
         Client_SYS.Add_To_Attr('BOOK_ID',book_id_,attr_);
         Client_SYS.Add_To_Attr('IS_FAVORITE',  'TRUE' ,  attr_);
         F8lib_Favorites_API.New__(info_ ,
                                   objid_,
                                   objversion_,
                                   attr_,
                                   'DO');
      END IF;
   ELSE 
      OPEN get_objs;
      FETCH get_objs INTO objid_,objversion_;
      CLOSE get_objs;
      Client_SYS.Clear_Attr(attr_);
      Client_SYS.Add_To_Attr('IS_FAVORITE',  'FALSE' ,  attr_);
      F8lib_Favorites_API.Modify__(info_ ,
                          objid_,
                          objversion_,
                          attr_,
                          'DO');
  END IF;
 
END Add_Favorites;