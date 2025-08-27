-----------------------------------------------------------------------------
--
--  Logical unit: F8libUsers
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
   newrec_ IN OUT f8lib_users_tab%ROWTYPE,
   indrec_ IN OUT Indicator_Rec,
   attr_   IN OUT VARCHAR2 )
IS
   v_count NUMBER;  
BEGIN
   --(+)20250729 ishak (basla)

 


   newrec_.user_id := 0;

   SELECT COUNT(*) INTO v_count
   FROM f8lib_users_tab
   WHERE email = newrec_.email;

   IF v_count > 0 THEN
      ERROR_SYS.Record_General( lu_name_,'DUPLICATE_EMAIL: Bu e-posta adresiyle zaten bir kayit mevcut!', NULL, NULL);
   END IF;

   -- Devam
   super(newrec_, indrec_, attr_);

   --(+)20250729 ishak (bitti)
END Check_Insert___;



@Override
PROCEDURE Insert___ (
   objid_      OUT    VARCHAR2,
   objversion_ OUT    VARCHAR2,
   newrec_     IN OUT f8lib_users_tab%ROWTYPE,
   attr_       IN OUT VARCHAR2 )
IS
   CURSOR get_max_user_id IS 
      SELECT MAX(user_id)
      FROM f8lib_users_tab;
   max_id_ NUMBER;
BEGIN
   
    OPEN get_max_user_id;
   FETCH get_max_user_id INTO max_id_;
   CLOSE get_max_user_id;
   newrec_.user_id:= NVL(max_id_,0)+1;
   Client_SYS.Add_To_Attr('USER_ID',   newrec_.user_id,       attr_);
   Client_SYS.Add_To_Attr('CREATION_DATE',   SYSDATE,       attr_);
    --(+)20250725 ishak (basla)
   super(objid_, objversion_, newrec_, attr_);
    --(+)20250725 ishak (bitti)
END Insert___;
-------------------- LU SPECIFIC PRIVATE METHODS ----------------------------


-------------------- LU SPECIFIC PROTECTED METHODS --------------------------


-------------------- LU SPECIFIC PUBLIC METHODS -----------------------------


PROCEDURE Add_Users(name_txt_        VARCHAR2,
                    email_txt_       VARCHAR2,
                    password_txt_    VARCHAR2,
                    user_activation_  NUMBER) 
IS
 info_       VARCHAR2(32000);
 objid_      VARCHAR2(32000);
 objversion_ VARCHAR2(32000);
 attr_       VARCHAR2(32000);
BEGIN
   Client_SYS.Clear_Attr(attr_);
   Client_SYS.Add_To_Attr('NAME',name_txt_, attr_);
    Client_SYS.Add_To_Attr('EMAIL',email_txt_, attr_);
    Client_SYS.Add_To_Attr('PASSWORD',password_txt_, attr_);
    Client_SYS.Add_To_Attr('USER_ACTIVATION', 
                       CASE WHEN user_activation_ = 1 THEN 'TRUE' ELSE 'FALSE' END, 
                       attr_);
   F8lib_Users_API.New__(info_ ,
                             objid_,
                             objversion_,
                             attr_,
                             'DO');
 
 END Add_Users;